<?php namespace App\Services\Order;

use App\Events\OrderCreated;
use App\Events\OrderUpdated;
use App\Http\Requests\OrderCreateRequest;
use App\Exceptions\OrderException;
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
use App\Jobs\Order\OrderPlacePushNotification;
use App\Models\Order;
use App\Services\BaseService;
use App\Services\Order\Constants\OrderLogTypes;
use App\Services\Order\Constants\SalesChannelIds;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService extends BaseService
{
    protected $orderRepository, $orderPaymentRepository, $customerRepository;
    protected $orderSkusRepositoryInterface;
    protected $updater, $orderSearch, $orderFilter;
    /** @var Creator */
    protected Creator $creator;

    public function __construct(OrderRepositoryInterface $orderRepository,
                                OrderSkusRepositoryInterface $orderSkusRepositoryInterface,
                                OrderSearch $orderSearch, CustomerRepositoryInterface $customerRepository,
                                OrderFilter $orderFilter,
                                Updater $updater, OrderPaymentRepositoryInterface $orderPaymentRepository,
                                Creator $creator,
    )
    {
        $this->orderRepository = $orderRepository;
        $this->orderSkusRepositoryInterface = $orderSkusRepositoryInterface;
        $this->updater = $updater;
        $this->orderSearch = $orderSearch;
        $this->orderFilter = $orderFilter;
        $this->creator = $creator;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->customerRepository = $customerRepository;
    }

    public function getOrderList($partner_id, $request)
    {
        list($offset, $limit) = calculatePagination($request);
        $orderSearch = $this->orderSearch->setOrderId($request->order_id)
            ->setCustomerName($request->customer_name)
            ->setQueryString($request->q)
            ->setSalesChannelId($request->sales_channel_id);

        $orderFilter = $this->orderFilter->setType($request->type)
            ->setOrderStatus($request->order_status)
            ->setPaymentStatus($request->payment_status);

        $ordersList = $this->orderRepository->getOrderListWithPagination($offset, $limit, $partner_id, $orderSearch, $orderFilter);
        $orderList = OrderResource::collection($ordersList);
        if (!$orderList) return $this->error("You're not authorized to access this order", 403);
        else return $this->success('Success', ['orders' => $orderList], 200);
    }

    public function getCustomerOrderList(string $customer_id, $request)
    {
        $orderBy = $request->filter;
        $order = $request->order;
        list($offset, $limit) = calculatePagination($request);
        $orderList = $this->orderRepository->getCustomerOrderList($customer_id, $offset, $limit, $orderBy, $order);
        if (count($orderList) == 0) return $this->error("You don't have any order", 404);
        $orderList = CustomerOrderResource::collection($orderList);
        return $this->success('Successful', ['orders' => $orderList], 200);
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
            ->setHeader($request->header('Authorization'))
            ->create();

        if ($order) event(new OrderCreated($order));
        if ($request->sales_channel_id == SalesChannelIds::WEBSTORE) dispatch(new OrderPlacePushNotification($order));
        return $this->success();
    }

    public function getOrderDetails($partner_id, $order_id)
    {
        $order = $this->orderRepository->where('partner_id', $partner_id)->find($order_id);
        if (!$order) return $this->error("You're not authorized to access this order", 403);
        $resource = new OrderWithProductResource($order);
        return $this->success('Successful', ['order' => $resource], 200);
    }

    public function getWebStoreOrderDetails(int $partner_id, int $order_id): JsonResponse
    {
        $order = $this->orderRepository->where('partner_id', $partner_id)->find($order_id);
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
        return $this->success('Successful', null, 200);
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
        $customer = $this->customerRepository->where('id', $order->customer_id)->first();
        return $this->success('Successful', ['customer' => $customer], 200);
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
}
