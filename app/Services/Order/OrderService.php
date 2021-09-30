<?php namespace App\Services\Order;

use App\Events\OrderDeleted;
use App\Http\Reports\InvoiceService;
use App\Http\Requests\OrderCreateRequest;
use App\Exceptions\OrderException;
use App\Http\Requests\OrderFilterRequest;
use App\Http\Requests\OrderStatusUpdateRequest;
use App\Http\Resources\CustomerOrderResource;
use App\Http\Requests\OrderUpdateRequest;
use App\Http\Resources\DeliveryResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderWithProductResource;
use App\Http\Resources\Webstore\CustomerOrderDetailsResource;
use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\OrderPaymentRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkusRepositoryInterface;
use App\Models\Order;
use App\Services\AccessManager\AccessManager;
use App\Services\AccessManager\Features;
use App\Services\APIServerClient\ApiServerClient;
use App\Services\BaseService;
use App\Services\Discount\Constants\DiscountTypes;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Constants\OrderLogTypes;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\OrderSms\WebstoreOrderSms;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Services\Order\Constants\Statuses;
use App\Services\Webstore\Order\Statuses as WebStoreStatuses;

class OrderService extends BaseService
{
    protected $orderRepository, $orderPaymentRepository, $customerRepository;
    protected $orderSkusRepositoryInterface;
    protected $updater, $orderFilter;
    /** @var Creator */
    protected Creator $creator;
    /**
     * @var InvoiceService
     */
    private InvoiceService $invoiceService;

    public function __construct(OrderRepositoryInterface        $orderRepository,
                                OrderSkusRepositoryInterface    $orderSkusRepositoryInterface,
                                CustomerRepositoryInterface     $customerRepository,
                                Updater                         $updater, OrderPaymentRepositoryInterface $orderPaymentRepository,
                                Creator                         $creator,
                                protected InventoryServerClient $client,
                                protected ApiServerClient       $apiServerClient,
                                protected AccessManager         $accessManager,
                                protected OrderFilter           $orderSearch,
                                protected StatusChanger $orderStatusChanger,
                                protected StockRefillerForCanceledOrder $stockRefillerForCanceledOrder,
                                InvoiceService                  $invoiceService
    )
    {
        $this->orderRepository = $orderRepository;
        $this->orderSkusRepositoryInterface = $orderSkusRepositoryInterface;
        $this->updater = $updater;
        $this->creator = $creator;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->customerRepository = $customerRepository;
        $this->invoiceService = $invoiceService;
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
            ->setSortBy($request->sort_by ?? OrderFilter::SORT_BY_CREATED_AT)
            ->setSortByOrder($request->sort_by_order ?? OrderFilter::SORT_BY_DESC)
            ->getOrderListWithPagination();

        $orderList = OrderResource::collection($search_result);
        if (!$orderList) return $this->error("You're not authorized to access this order", 403);
        else return $this->success('Success', ['orders' => $orderList], 200);
    }

    public function getCustomerOrderList(string $customer_id, $request): JsonResponse
    {
        $orderBy = $request->filter;
        $order = $request->order;
        list($offset, $limit) = calculatePagination($request);
        $orderList = $this->orderRepository->getCustomerOrderList($customer_id, $offset, $limit, $orderBy, $order);
        $orderCount = count($this->orderRepository->getCustomerOrderCount($customer_id));
        if (count($orderList) == 0) return $this->error("You don't have any order", 404);
        $orderList = CustomerOrderResource::collection($orderList);
        return $this->success('Successful', ['order_count' => $orderCount, 'orders' => $orderList], 200);
    }


    /**
     * @param $partner
     * @param OrderCreateRequest $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws OrderException
     */
    public function store($partner, OrderCreateRequest $request): JsonResponse
    {
        $order = $this->creator->setPartner($partner)
            ->setCustomerId($request->customer_id)
            ->setDeliveryName($request->delivery_name)
            ->setDeliveryMobile($request->delivery_mobile)
            ->setDeliveryAddress($request->delivery_address)
            ->setCustomerId($request->customer_id)
            ->setSalesChannelId($request->sales_channel_id)
            ->setDeliveryCharge($this->calculateDeliveryCharge($request,$partner))
            ->setCodAmount($request->cod_amount)
            ->setEmiMonth($request->emi_month)
            ->setSkus($request->skus)
            ->setDiscount($request->discount)
            ->setPaidAmount($request->paid_amount)
            ->setPaymentMethod($request->payment_method)
            ->setVoucherId($request->voucher_id)
            ->setApiRequest($request->api_request->id)
            ->create();
        if ($request->sales_channel_id == SalesChannelIds::WEBSTORE)
        {
            //dispatch(new OrderPlacePushNotification($order));
            dispatch(new WebstoreOrderSms($partner,$order->id));
        }
        return $this->success('Successful', ['order' => ['id' => $order->id]]);
    }

    private function calculateDeliveryCharge($request, $partner_id)
    {
        $data = [
            'weight' => $request->weight,
            'delivery_district' => $request->delivery_district,
            'delivery_thana' => $request->delivery_thana,
            'partner_id' => $partner_id,
            'cod_amount' => $request->cod_amount
        ];
        return $this->apiServerClient->setBaseUrl()->post('v2/pos/delivery/delivery-charge', $data)['delivery_charge'];
    }

    /**
     * @param int $order_id
     * @return JsonResponse
     */
    public function getWebsotreOrderInvoice(int $order_id): JsonResponse
    {
        $order = $this->orderRepository->where('sales_channel_id', SalesChannelIds::WEBSTORE)->find($order_id);
        if (!$order) return $this->error('No Order Found',404);
        if ($order->invoice == null) {
            return $this->invoiceService->setOrder($order_id)->generateInvoice();
        }
        return $this->success('Successful', ['invoice' => $order->invoice], 200);
    }

    public function getOrderInvoice(int $order_id): JsonResponse
    {
        $order = $this->orderRepository->where('sales_channel_id', SalesChannelIds::POS)->find($order_id);
        if (!$order) return $this->error('No Order Found',404);
        if ($order->invoice == null) {
            return $this->invoiceService->setOrder($order_id)->generateInvoice();
        }
        $this->accessManager->setPartnerId($order->partner_id)->setFeature(Features::INVOICE_DOWNLOAD)->checkAccess();
        return $this->success('Successful', ['invoice' => $order->invoice], 200);
    }


    public function getOrderDetails($partner_id, $order_id): JsonResponse
    {
        $order = $this->orderRepository->getOrderDetailsByPartner($partner_id, $order_id);
        if (!$order) return $this->error("You're not authorized to access this order", 403);
        $resource = new OrderWithProductResource($order);
        $resource = $this->addUpdatableFlagForItems($resource, $order);
        return $this->success('Successful', ['order' => $resource], 200);
    }

    public function getWebStoreOrderDetails(int $partner_id, int $order_id, string $customer_id): JsonResponse
    {
        $order = $this->orderRepository->where('partner_id', $partner_id)->where('customer_id', $customer_id)->with('statusChangeLogs')->find($order_id);
        if (!$order) return $this->error("You're not authorized to access this order", 403);
        $resource = new CustomerOrderDetailsResource($order);
        $statusHistory = $this->getStatusHistory($order);
        return $this->success('Successful', ['order' => $resource,'status_history' => $statusHistory]);
    }

    private function getStatusHistory($order): array
    {
        $logs = $order->statusChangeLogs;
        $statusHistory = [];
        $temp['status'] = WebStoreStatuses::ORDER_PLACED;
        $temp['time_stamp'] = $order->created_at;
        array_push($statusHistory, $temp);
        $mapped_status = config('mapped_status');
        $logs->each(function ($log) use (&$statusHistory, $order, $mapped_status) {
            $toStatus = json_decode($log->new_value, true)['to'];
            if (in_array($toStatus, [Statuses::PROCESSING, Statuses::SHIPPED, Statuses::COMPLETED])){
                $temp['status'] = $mapped_status[$toStatus];
                $temp['time_stamp'] =  convertTimezone($log->created_at)->format('Y-m-d H:i:s');
                array_push($statusHistory, $temp);
            }
        });
        return $statusHistory;
    }

    /**
     * @param OrderUpdateRequest $orderUpdateRequest
     * @param $partner_id
     * @param $order_id
     * @return JsonResponse
     * @throws Exception
     */
    public function update(OrderUpdateRequest $orderUpdateRequest, $partner_id, $order_id): JsonResponse
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

    public function delete($partner_id, $order_id): JsonResponse
    {
        $order = $this->orderRepository->where('partner_id', $partner_id)->find($order_id);
        if (!$order) return $this->error("You're not authorized to access this order", 403);
        $this->stockRefillerForCanceledOrder->setOrder($order)->refillStock();
        event(new OrderDeleted($order));
        $order->delete();
        return $this->success();
    }

    public function getOrderInfoForPaymentLink($order_id): JsonResponse
    {
        $orderDetails = $this->orderRepository->find($order_id);
        if (!$orderDetails) return $this->error("Order Not Found", 404);
        $order = [
            'id' => $orderDetails->id,
            'sales_channel' => $orderDetails->sales_channel_id == 1 ? 'pos' : 'webstore',
            'created_at' => $orderDetails->created_at,
            'customer' => [
                'id' => $orderDetails->customer_id,
                'name' => $orderDetails?->customer?->name,
                'mobile' => $orderDetails?->customer?->mobile
            ],
            'partner' => [
                'id' => $orderDetails?->partner?->id,
                'sub_domain' => $orderDetails?->partner?->sub_domain
            ]
        ];
        return $this->success('Successful', ['order' => $order]);
    }

    /**
     * @throws OrderException
     * @throws Exception
     */
    public function updateCustomer($customer_id, $partner_id, $order_id): JsonResponse
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
        $this->orderRepository->where('partner_id', $partner_id)->find($order_id);
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
                    $sku_discount = $order_sku_discounts->where('item_id', $item['id'])->first();
                    if(($channels_discount && $sku_discount) && ($sku_discount->amount != $channels_discount['amount'] || $sku_discount->is_percentage !== $channels_discount['is_amount_percentage'])) {
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

    public function updateOrderStatus($partner_id, $order_id, OrderStatusUpdateRequest $request)
    {
        $order = $this->orderRepository->where('id', $order_id)->where('partner_id',$partner_id)->first();
        if (!$order) return $this->error("No Order Found", 404);
        $this->orderStatusChanger->setOrder($order)->setStatus($request->status)->changeStatus();
        return $this->success('Successful', [], 200);
    }

    public function updateOrderStatusForIpn(int $partner_id, string $delivery_req_id, Request $request)
    {
        $request->validate([
           'status' => Rule::in(Statuses::COMPLETED)
        ]);
        $order = $this->orderRepository->where('delivery_request_id', $delivery_req_id)->where('partner_id', $partner_id)->first();
        if (!$order) return $this->error("No Order Found", 404);
        $this->orderStatusChanger->setDeliveryRequestId($delivery_req_id)->setStatus(Statuses::COMPLETED)->setOrder($order)->updateStatusForIpn();
        return $this->success('Successful', []);
    }

    public function logs(int $order_id)
    {
        $logs = [
            [
                'log_type' => 'due_bill',
                'log_type_show_name' => ['bn' => 'বাকি বিল', 'en' => 'Due Bill'],
                'old_value' => null,
                'new_value' => 500.00,
                'created_at' => '2020-01-05 12:45:05',
                'created_by_name' => 'Resource - Abdullah Arnab'
            ],
            [
                'log_type' => 'payments',
                'log_type_show_name' => ['bn' => 'নগদ  গ্রহণ', 'en' => 'Cash Collection'],
                'old_value' => null,
                'new_value' => 500.00,
                'created_at' => '2020-01-05 12:46:05',
                'created_by_name' => 'Resource - Abdullah Arnab'
            ],
            [
                'log_type' => 'payments',
                'log_type_show_name' => ['bn' => 'অনলাইন গ্রহন', 'en' => 'Online Collection'],
                'old_value' => null,
                'new_value' => 500.00,
                'created_at' => '2020-01-05 12:47:05',
                'created_by_name' => 'Resource - Abdullah Arnab'
            ],
            [
                'log_type' => 'payable',
                'log_type_show_name' => ['bn' => 'অর্ডার আপডেট (Increase)', 'en' => 'Order Update (Increase)'],
                'old_value' => null,
                'new_value' => 500.00,
                'created_at' => '2020-01-05 12:48:05',
                'created_by_name' => 'Resource - Abdullah Arnab'
            ],
            [
                'log_type' => 'payable',
                'log_type_show_name' => ['bn' => 'অর্ডার আপডেট (Decrease)', 'en' => 'Order Update (Decrease)'],
                'old_value' => null,
                'new_value' => 500.00,
                'created_at' => '2020-01-05 12:49:05',
                'created_by_name' => 'Resource - Abdullah Arnab'
            ],
            [
                'log_type' => 'emi',
                'log_type_show_name' => ['bn' => 'কিস্তি - ৩ মাস', 'en' => 'Emi - 3 Months'],
                'old_value' => null,
                'new_value' => 5000.00,
                'created_at' => '2020-01-05 12:45:05',
                'created_by_name' => 'Resource - Abdullah Arnab'
            ],
            [
                'log_type' => 'status_update',
                'log_type_show_name' => ['bn' => 'স্ট্যাটাস আপডেট ', 'en' => 'Status Update'],
                'old_value' => Statuses::PROCESSING,
                'new_value' => Statuses::SHIPPED,
                'created_at' => '2020-01-05 12:50:05',
                'created_by_name' => 'Resource - Abdullah Arnab'
            ]
        ];
        return $this->success('Successful', ['logs' => $logs], 200);
    }
}
