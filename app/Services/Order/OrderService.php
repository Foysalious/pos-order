<?php namespace App\Services\Order;

use App\Events\OrderCreated;
use App\Events\OrderUpdated;
use App\Exceptions\AuthorizationException;
use App\Http\Reports\GenerateInvoice;
use App\Helper\Miscellaneous\RequestIdentification;
use App\Http\Requests\OrderCreateRequest;
use App\Exceptions\OrderException;
use App\Http\Requests\OrderFilterRequest;
use App\Http\Resources\CustomerOrderResource;
use App\Http\Requests\OrderUpdateRequest;
use App\Http\Resources\DeliveryResource;
use App\Http\Resources\OrderInvoiceResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderWithProductResource;
use App\Http\Resources\Webstore\CustomerOrderDetailsResource;
use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\OrderPaymentRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkusRepositoryInterface;
use App\Jobs\Order\OrderPlacePushNotification;
use App\Models\Order;
use App\Services\AccessManager\AccessManager;
use App\Services\AccessManager\Features;
use App\Services\APIServerClient\ApiServerClient;
use App\Services\BaseService;
use App\Services\Discount\Constants\DiscountTypes;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Constants\OrderLogTypes;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;

class OrderService extends BaseService
{
    protected $orderRepository, $orderPaymentRepository, $customerRepository;
    protected $orderSkusRepositoryInterface;
    protected $updater, $orderFilter;
    /** @var Creator */
    protected Creator $creator;

    public function __construct(OrderRepositoryInterface     $orderRepository,
                                OrderSkusRepositoryInterface $orderSkusRepositoryInterface,
                                CustomerRepositoryInterface $customerRepository,
                                Updater $updater, OrderPaymentRepositoryInterface $orderPaymentRepository,
                                Creator $creator,
                                protected InventoryServerClient $client,
                                protected ApiServerClient $apiServerClient,
                                protected AccessManager $accessManager,
                                protected OrderSearch $orderSearch
    )
    {
        $this->orderRepository = $orderRepository;
        $this->orderSkusRepositoryInterface = $orderSkusRepositoryInterface;
        $this->updater = $updater;
        $this->creator = $creator;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->customerRepository = $customerRepository;
    }

    public function getOrderList(int $partner_id, OrderFilterRequest $request): JsonResponse
    {
        list($offset, $limit) = calculatePagination($request);
        $search_result = $this->orderSearch->setPartnerId($partner_id)
            ->setQueryString($request->q)
            ->setType($request->type)
            ->setSalesChannelId($request->sales_channel_id)
            ->setPaymentStatus($request->payment_status)
            ->setOrderStatus($request->order_status)
            ->setOffset($offset)
            ->setLimit($limit)
            ->getOrderListWithPagination();

        $orderList = OrderResource::collection($search_result);
        if (!$orderList) return $this->error("You're not authorized to access this order", 403);
        else return $this->success('Success', ['orders' => $orderList], 200);
    }

    public function getCustomerOrderList(string $customer_id, $request)
    {
        $orderBy = $request->filter;
        $order = $request->order;
        list($offset, $limit) = calculatePagination($request);
        $orderList = $this->orderRepository->getCustomerOrderList($customer_id, $offset, $limit, $orderBy, $order);
        $orderCount = count($this->orderRepository->getCustomerOrderCount($customer_id));
        if (count($orderList) == 0) return $this->error("You don't have any order", 404);
        $orderList = CustomerOrderResource::collection($orderList);
        return $this->success('Successful', ['order_count' => $orderCount,'orders' => $orderList ], 200);
    }


    /**
     * @param $partner
     * @param OrderCreateRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store($partner, OrderCreateRequest $request)
    {
        $skus = is_array($request->skus) ? $request->skus : json_decode($request->skus);
        $header = $request->header('Authorization');
        $order = $this->creator->setPartner($partner)
            ->setCustomerId($request->customer_id)
            ->setDeliveryName($request->delivery_name)
            ->setDeliveryMobile($request->delivery_mobile)
            ->setDeliveryAddress($request->delivery_address)
            ->setCustomerId($request->customer_id)
            ->setSalesChannelId($request->sales_channel_id)
            ->setDeliveryCharge($request->delivery_charge)
            ->setEmiMonth($request->emi_month)
            ->setSkus($skus)
            ->setDiscount($request->discount)
            ->setPaidAmount($request->paid_amount)
            ->setPaymentMethod($request->payment_method)
            ->setVoucherId($request->voucher_id)
            ->setApiRequest($request->api_request->id)
            ->create();

//        if ($order) event(new OrderCreated($order));
//        if ($request->sales_channel_id == SalesChannelIds::WEBSTORE) dispatch(new OrderPlacePushNotification($order));
        return $this->success('Successful', ['order' => ['id' => $order->id]]);
    }

    /**
     * @throws AuthorizationException
     */
    public function getOrderInvoice($order_id)
    {
        $order = $this->orderRepository->find($order_id);
        if (!$order) return $this->error("No Order Found", 404);
        $this->accessManager->setPartnerId($order->partner_id)->setFeature(Features::INVOICE_DOWNLOAD)->checkAccess();
        return $this->success('Successful', ['invoice' =>  $order->invoice], 200);
    }


    public function getOrderDetails($partner_id, $order_id)
    {
        $order = $this->orderRepository->getOrderDetailsByPartner($partner_id, $order_id);
        if (!$order) return $this->error("You're not authorized to access this order", 403);
        $resource = new OrderWithProductResource($order);
        $resource = $this->addUpdatableFlagForItems($resource,$order);
        return $this->success('Successful', ['order' => $resource], 200);
    }

    public function getWebStoreOrderDetails(int $partner_id, int $order_id, string $customer_id): JsonResponse
    {

        $order = $this->orderRepository->where('partner_id', $partner_id)->where('customer_id', $customer_id)->find($order_id);
        if (!$order) return $this->error("You're not authorized to access this order", 403);
        $resource = new CustomerOrderDetailsResource($order);
        return $this->success('Successful', ['order' => $resource], 200);

    }

    /**
     * @param OrderUpdateRequest $orderUpdateRequest
     * @param $partner_id
     * @param $order_id
     * @return JsonResponse
     */
    public function update(OrderUpdateRequest $orderUpdateRequest, $partner_id, $order_id)
    {
        $orderDetails = $this->orderRepository->where('partner_id', $partner_id)->find($order_id);
        if (!$orderDetails) return $this->error("You're not authorized to access this order", 403);
        $this->updater->setPartnerId($partner_id)
            ->setOrderId($order_id)
            ->setOrder($orderDetails)
            ->setSalesChannelId($orderUpdateRequest->sales_channel_id)
            ->setSkus($orderUpdateRequest->skus ?? null)
            ->setEmiMonth($orderUpdateRequest->emi_month)
            ->setInterest($orderUpdateRequest->interest)
            ->setDeliveryCharge($orderUpdateRequest->delivery_charge)
            ->setBankTransactionCharge($orderUpdateRequest->bank_transaction_charge)
            ->setDeliveryName($orderUpdateRequest->delivery_name)
            ->setDeliveryMobile($orderUpdateRequest->delivery_mobile)
            ->setDeliveryAddress($orderUpdateRequest->delivery_address)
            ->setNote($orderUpdateRequest->note)
            ->setVoucherId($orderUpdateRequest->voucher_id)
            ->setPaidAmount($orderUpdateRequest->paid_amount ?? null)
            ->setPaymentMethod($orderUpdateRequest->payment_method ?? null)
            ->setPaymentLinkAmount($orderUpdateRequest->payment_link_amount ?? null)
            ->setDiscount($orderUpdateRequest->discount)
            ->setHeader($orderUpdateRequest->header('Authorization'))
            ->setDeliveryVendorName($orderUpdateRequest->delivery_vendor_name ?? null)
            ->setDeliveryRequestId($orderUpdateRequest->delivery_request_id ?? null)
            ->setDeliveryThana($orderUpdateRequest->delivery_thana ?? null)
            ->setDeliveryDistrict($orderUpdateRequest->delivery_district ?? null)
            ->update();
        return $this->success('Successful', [], 200);
    }

    public function delete($partner_id, $order_id)
    {
        $order = $this->orderRepository->where('partner_id', $partner_id)->find($order_id);
        if (!$order) return $this->error("You're not authorized to access this order", 403);
        $OrderSkusIds = $this->orderSkusRepositoryInterface->where('order_id', $order_id)->get(['id']);
        $this->orderSkusRepositoryInterface->whereIn('id', $OrderSkusIds)->delete();
        $order->delete();
        return $this->success();
    }

    public function getOrderWithChannel($order_id)
    {
        $orderDetails = $this->orderRepository->find($order_id);
        if (!$orderDetails) return $this->error("You're not authorized to access this order", 403);
        $order = [
            'id' => $orderDetails->id,
            'sales_channel' => $orderDetails->sales_channel_id == 1 ? 'pos' : 'webstore'
        ];
        return $this->success('Success', ['order' => $order], 200);
    }

    public function updateCustomer($customer_id, $partner_id, $order_id)
    {
        $order = $this->orderRepository->where('partner_id', $partner_id)->find($order_id);
        if (!$order) return $this->error(trans('order.order_not_found'), 404);
        if (!$this->customerRepository->find($customer_id)) return $this->error(trans('order.customer_not_found'), 404);
        if ($this->checkCustomerHasPayment($order_id))
            $this->updater->setOrderId($order_id)
                ->setOrder($order)
                ->setCustomerId($customer_id)
                ->setOrderLogType(OrderLogTypes::CUSTOMER)
                ->update();
        $order = $this->orderRepository->where('partner_id', $partner_id)->find($order_id);
        return $this->success();
    }

    public function getDeliveryInfo(int $partner_id, int $order_id): JsonResponse
    {
        $order = $this->orderRepository->where('partner_id', $partner_id)->find($order_id);
        if (!$order) return $this->error("You're not authorized to access this order", 403);
        $resource = new DeliveryResource($order);
        return $this->success('Successful', ['order' => $resource], 200);

    }


    private function checkCustomerHasPayment($order_id): bool
    {
        $orderPaymentStatus = $this->orderPaymentRepository->where('order_id', $order_id)->get();
        if (count($orderPaymentStatus) > 0) throw new OrderException(trans('order.update.no_customer_update'));
        else return true;
    }

    private function addUpdatableFlagForItems($order_resource, Order $order)
    {
        $order_resource = json_decode(($order_resource->toJson()), true);
        $sku_ids = $order->orderSkus->whereNotNull('sku_id')->pluck('sku_id');
        $sku_details = $sku_ids->count() > 0 ? $this->getSkuDetails($sku_ids, $order) : collect();
        $order_sku_discounts = $order->discounts->where('type', DiscountTypes::SKU);
        foreach ($order_resource['items'] as &$item) {
            $flag = true;
           if ($item['sku_id'] !== null) {
                $sku = $sku_details->where('id', $item['sku_id'])->first();
                if ($sku['sku_channel'][0]['price'] != $item['unit_price']){
                    $flag = false;
                } else {
                    $channels_discount = collect($sku['sku_channel'])->where('channel_id', $order->sales_channel_id)->pluck('discounts')->first()[0] ?? [];
                    $sku_discount = $order_sku_discounts->where('item_id', $item['sku_id'])->first();
                    if($channels_discount && ($sku_discount->amount != $channels_discount['amount'] || $sku_discount->is_percentage !== $channels_discount['is_amount_percentage'])) {
                        $flag = false;
                    }
                }

           }
            $item['is_updatable'] = $flag;
        }
        return $order_resource;
    }

    private function getSkuDetails($sku_ids, $order)
    {
        $url = 'api/v1/partners/' . $order->partner_id . '/skus?skus=' . json_encode($sku_ids->toArray()) . '&channel_id='.$order->sales_channel_id;
        $sku_details = $this->client->setBaseUrl()->get($url)['skus'] ?? [];
        return collect($sku_details);
    }
}
