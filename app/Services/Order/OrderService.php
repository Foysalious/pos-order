<?php namespace App\Services\Order;

use App\Constants\ResponseMessages;
use App\Events\OrderDeleted;
use App\Http\Reports\InvoiceService;
use App\Http\Requests\DeliveryStatusUpdateIpnRequest;
use App\Http\Requests\OrderCreateRequest;
use App\Exceptions\OrderException;
use App\Http\Requests\OrderCustomerUpdateRequest;
use App\Http\Requests\OrderFilterRequest;
use App\Http\Requests\OrderStatusUpdateRequest;
use App\Http\Resources\CustomerOrderResource;
use App\Http\Requests\OrderUpdateRequest;
use App\Http\Resources\DeliveryResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderWithProductResource;
use App\Http\Resources\Webstore\CustomerOrderDetailsResource;
use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\OrderLogRepositoryInterface;
use App\Interfaces\OrderPaymentRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkusRepositoryInterface;
use App\Jobs\Order\OrderEmail;
use App\Jobs\Order\OrderPlacePushNotification;
use App\Models\Order;
use App\Models\OrderLog;
use App\Services\AccessManager\AccessManager;
use App\Services\AccessManager\Features;
use App\Services\APIServerClient\ApiServerClient;
use App\Services\BaseService;
use App\Services\ClientServer\Exceptions\BaseClientServerError;
use App\Services\Customer\CustomerResolver;
use App\Services\Delivery\Methods;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Constants\OrderLogTypes;
use App\Services\Order\Constants\PaymentStatuses;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\OrderLog\Objects\OrderObjectRetriever;
use App\Services\OrderLog\OrderLogGenerator;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\Order\Constants\Statuses;
use App\Services\Webstore\Order\States as WebStoreStatuses;
use App\Services\Webstore\Order\StateTags;

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

    public function __construct(
        OrderRepositoryInterface                $orderRepository,
        OrderSkusRepositoryInterface            $orderSkusRepositoryInterface,
        CustomerRepositoryInterface             $customerRepository,
        Updater                                 $updater,
        OrderPaymentRepositoryInterface         $orderPaymentRepository,
        Creator                                 $creator,
        protected InventoryServerClient         $client,
        protected ApiServerClient               $apiServerClient,
        protected AccessManager                 $accessManager,
        protected OrderFilter                   $orderSearch,
        protected StatusChanger                 $orderStatusChanger,
        InvoiceService                          $invoiceService,
        protected ApiServerClient               $apiServerClientclient,
        protected CustomerResolver              $customerResolver,
        private OrderLogRepositoryInterface     $orderLogRepository
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
            ->setOrderStatus($request->order_status == 'All' ? null : $request->order_status)
            ->setOffset($offset)
            ->setLimit($limit)
            ->setSortBy($request->sort_by ?? OrderFilter::SORT_BY_CREATED_AT)
            ->setSortByOrder($request->sort_by_order ?? OrderFilter::SORT_BY_DESC)
            ->getOrderListWithPagination();

        $orderList = OrderResource::collection($search_result);
        if (!$orderList) return $this->error("You're not authorized to access this order", 403);
        else return $this->success(ResponseMessages::SUCCESS, ['orders' => $orderList]);
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
        return $this->success(ResponseMessages::SUCCESS, ['order_count' => $orderCount, 'orders' => $orderList]);
    }


    /**
     * @param $partner
     * @param OrderCreateRequest $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws OrderException|BaseClientServerError
     */
    public function store($partner, OrderCreateRequest $request): JsonResponse
    {
        $order = $this->creator->setPartnerId($partner)
            ->setDeliveryName($request->delivery_name)
            ->setDeliveryMobile($request->delivery_mobile)
            ->setDeliveryAddress($request->delivery_address)
            ->setCustomerId($request->customer_id)
            ->setSalesChannelId($request->sales_channel_id)
            ->setDeliveryDistrict($request->delivery_district)
            ->setDeliveryThana($request->delivery_thana)
            ->setTotalWeight($request->total_weight)
            ->setDeliveryMethod($request->delivery_method)
            ->setCodAmount($request->cod_amount)
            ->setEmiMonth($request->emi_month)
            ->setSkus($request->skus)
            ->setDiscount($request->discount)
            ->setPaidAmount($request->paid_amount)
            ->setPaymentMethod($request->payment_method)
            ->setVoucherId($request->voucher_id)
            ->setApiRequest($request->api_request->id)
            ->create();
        return $this->success(ResponseMessages::SUCCESS, ['order' => ['id' => $order->id]]);
    }

    /**
     * @param int $order_id
     * @return JsonResponse
     */
    public function getWebsotreOrderInvoice(int $order_id): JsonResponse
    {
        $order = $this->orderRepository->where('sales_channel_id', SalesChannelIds::WEBSTORE)->find($order_id);
        if (!$order) return $this->error('No Order Found', 404);
        if ($order->invoice == null) {
            return $this->invoiceService->setOrder($order)->generateInvoice();
        }
        return $this->success(ResponseMessages::SUCCESS, ['invoice' => $order->invoice]);
    }

    private function getSkuDetailsForWebstore($partner, $sku_ids)
    {
        $url = 'api/v1/partners/' . $partner . '/webstore-skus-details?skus=' . json_encode($sku_ids->toArray()) . '&channel_id=' . 2;
        $sku_details = $this->client->get($url)['skus'] ?? [];
        return $this->success(ResponseMessages::SUCCESS, ['data' => collect($sku_details)]);
    }

    public function getTrendingProducts(int $partner_id)
    {
        $trending = $this->orderSkusRepositoryInterface->getTrendingProducts($partner_id);
        $products = $this->getSkuDetailsForWebstore($partner_id, $trending);
        if ($trending->count() > 0) {

            if (empty($products->getData()->data)) return $this->error('no product Found');
            else return $products;
        } else return $this->error('no product Found');
    }

    public function getOrderInvoice(int $partner_id, int $order_id): JsonResponse
    {
        $order = $this->orderRepository->where('sales_channel_id', SalesChannelIds::POS)->where('partner_id', $partner_id)->find($order_id);
        if (!$order) return $this->error('No Order Found', 404);
        if ($order->invoice == null) {
            return $this->invoiceService->setOrder($order)->generateInvoice();
        }
        $this->accessManager->setPartnerId($order->partner_id)->setFeature(Features::INVOICE_DOWNLOAD)->checkAccess();
        return $this->success(ResponseMessages::SUCCESS, ['invoice' => $order->invoice]);
    }


    public function getOrderDetails($partner_id, $order_id): JsonResponse
    {
        $order = $this->orderRepository->getOrderDetailsByPartner($partner_id, $order_id);
        if (!$order) return $this->error("You're not authorized to access this order", 403);
        if ($order->invoice == null) {
            try {
                app(InvoiceService::class)->setOrder($order)->generateInvoice();
            } catch (Exception $exception) {
            }
        }
        $resource = new OrderWithProductResource($order, true);
        return $this->success(ResponseMessages::SUCCESS, ['order' => $resource]);
    }

    public function getOrderInfo($partner_id, $order_id)
    {
        return $this->orderRepository->getOrderDetailsByPartner($partner_id, $order_id);
    }

    public function getWebStoreOrderDetails(int $partner_id, int $order_id, string $customer_id): JsonResponse
    {
        $order = $this->orderRepository->where('partner_id', $partner_id)->where('customer_id', $customer_id)->with('statusChangeLogs')->find($order_id);
        if (!$order) return $this->error("You're not authorized to access this order", 403);
        $resource = new CustomerOrderDetailsResource($order);
        $stateHistory = $this->getStateHistory($order);
        return $this->success(ResponseMessages::SUCCESS, ['order' => $resource, 'state_history' => $stateHistory]);
    }

    private function getStateHistory($order): array
    {
        $logs = $order->statusChangeLogs;
        $statusHistory = [];
        $temp['state_text'] = WebStoreStatuses::ORDER_PLACED;
        $temp['state_tag'] = StateTags::ORDER_PLACED;
        $temp['time_stamp'] = convertTimezone($order->created_at)?->format('Y-m-d H:i:s');
        array_push($statusHistory, $temp);
        $mapped_state = config('mapped_status');
        $mapped_state_tag = config('mapped_state_tag');
        $logs->each(function ($log) use (&$statusHistory, $order, $mapped_state, $mapped_state_tag) {
            $toStatus = json_decode($log->new_value, true)['status'];
            if (in_array($toStatus, [Statuses::PROCESSING, Statuses::SHIPPED, Statuses::COMPLETED])) {
                $temp['state_text'] = $mapped_state[$toStatus];
                $temp['state_tag'] = $mapped_state_tag[$temp['state_text']];
                $temp['time_stamp'] = convertTimezone(Carbon::parse($log->created_at))?->format('Y-m-d H:i:s');
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
        $orderDetails = $this->orderRepository->where('partner_id', $partner_id)->find($order_id)->load(['items' => function ($q) {
            $q->with('discount');
        }, 'customer', 'payments', 'discounts']);
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
            ->setDiscount($orderUpdateRequest->discount)
            ->setDeliveryVendorName($orderUpdateRequest->delivery_vendor_name ?? null)
            ->setDeliveryRequestId($orderUpdateRequest->delivery_request_id ?? null)
            ->setDeliveryThana($orderUpdateRequest->delivery_thana ?? null)
            ->setDeliveryDistrict($orderUpdateRequest->delivery_district ?? null)
            ->setCustomerId($orderUpdateRequest->customer_id)
            ->update();
        return $this->success();
    }

    public function delete($partner_id, $order_id): JsonResponse
    {
        DB::beginTransaction();
        $order = $this->orderRepository->where('partner_id', $partner_id)->find($order_id);
        if (!$order) return $this->error("You're not authorized to access this order", 403);
        $order->delete();
        event(new OrderDeleted($order));
        DB::commit();
        return $this->success();
    }

    public function getOrderInfoForPaymentLink($order_id): JsonResponse
    {
        $orderDetails = $this->orderRepository->find($order_id);
        if (!$orderDetails) return $this->error("Order Not Found", 404);
        $order = $this->getOrderData($orderDetails);
        return $this->success(ResponseMessages::SUCCESS, ['order' => $order]);
    }

    private function getOrderData($orderDetails)
    {
        /** @var PriceCalculation $price_calculator */
        $price_calculator = app(PriceCalculation::class);
        $due = $price_calculator->setOrder($orderDetails)->getDue();
        return [
            'id' => $orderDetails->id,
            'partner_wise_order_id' => $orderDetails->partner_wise_order_id,
            'sales_channel' => $orderDetails->sales_channel_id == 1 ? 'pos' : 'webstore',
            'created_at' => $orderDetails->created_at,
            'partner_id' => $orderDetails->partner_id,
            'customer_id' => $orderDetails->customer_id,
            'emi_month' => $orderDetails->emi_month,
            'customer' => [
                'id' => $orderDetails->customer_id,
                'name' => $orderDetails?->customer?->name,
                'mobile' => $orderDetails?->customer?->mobile,
                'pro_pic' => $orderDetails?->customer?->pro_pic,
            ],
            'partner' => [
                'id' => $orderDetails?->partner?->id,
                'sub_domain' => $orderDetails?->partner?->sub_domain
            ],
            'due' => $due
        ];
    }

    public function getOrderInfoByPartnerWiseOrderId($partnerId, $partnerWiseOrderId)
    {
        $orderDetails = $this->orderRepository
            ->where('partner_wise_order_id', $partnerWiseOrderId)
            ->where('partner_id', $partnerId)->first();
        if (!$orderDetails) return $this->error("Order Not Found", 404);
        $order = $this->getOrderData($orderDetails);
        return $this->success(ResponseMessages::SUCCESS, ['order' => $order]);
    }

    /**
     * @throws Exception
     */
    public function updateCustomer($partner_id, $order_id, OrderCustomerUpdateRequest $request): JsonResponse
    {
        $customer_id = $request->customer_id;
        $order = $this->orderRepository->where('partner_id', $partner_id)->find($order_id)->load(['items', 'customer', 'payments', 'discounts']);
        if (!$order) return $this->error(trans('order.order_not_found'), 404);
        if (!is_null($customer_id)) {
            $customer = $this->customerResolver->setCustomerId($customer_id)->setPartnerId($partner_id)->resolveCustomer();
            if ($customer->id == $order->customer?->id) return $this->error(trans('invalid customer update request'), 400);
        }
        $this->updater->setOrderId($order_id)
            ->setOrder($order)
            ->setCustomerId($customer_id)
            ->setOrderLogType(OrderLogTypes::CUSTOMER)
            ->updateCustomer(true);
        return $this->success();
    }

    public function getDeliveryInfo(int $partner_id, int $order_id): JsonResponse
    {
        $order = $this->orderRepository->where('partner_id', $partner_id)->find($order_id);
        if (!$order) return $this->error("You're not authorized to access this order", 403);
        $resource = new DeliveryResource($order);
        return $this->success(ResponseMessages::SUCCESS, ['order' => $resource]);

    }

    public function updateOrderStatus($partner_id, $order_id, OrderStatusUpdateRequest $request): JsonResponse
    {
        $order = $this->orderRepository->where('id', $order_id)->where('partner_id', $partner_id)->first();
        if (!$order) return $this->error("No Order Found", 404);

        if (!$this->canChangeToThisStatus($order, $request->status))
            return $this->error('Not allowed to changed to this status', 403);
        $this->orderStatusChanger->setOrder($order)->setStatus($request->status)->changeStatus();
        return $this->success();
    }

    private function canChangeToThisStatus(Order $orderBeforeUpdated, $toStatus): bool
    {
        if (!$orderBeforeUpdated->isWebStore()) return false;
        $fromStatus = $orderBeforeUpdated->status;
        $delivery_vendor_name = $orderBeforeUpdated->delivery_vendor && isset(json_decode($orderBeforeUpdated->delivery_vendor, true)['name']) ? json_decode($orderBeforeUpdated->delivery_vendor, true)['name'] : null;
        if ($delivery_vendor_name == Methods::OWN_DELIVERY) {
            if ($fromStatus == Statuses::PENDING && in_array($toStatus, [Statuses::PROCESSING, Statuses::DECLINED])) return true;
            if ($fromStatus == Statuses::PROCESSING && in_array($toStatus, [Statuses::SHIPPED, Statuses::CANCELLED])) return true;
            if ($fromStatus == Statuses::SHIPPED && $toStatus == Statuses::COMPLETED) return true;
        } else {
            if ($fromStatus == Statuses::PENDING && in_array($toStatus, [Statuses::PROCESSING, Statuses::DECLINED])) return true;
            if ($fromStatus == Statuses::PROCESSING && $toStatus == Statuses::CANCELLED) return true;
        }
        return false;
    }

    public function updateOrderStatusByIpn(int $partner_id, DeliveryStatusUpdateIpnRequest $request): JsonResponse
    {
        $order = $this->orderRepository->where('delivery_request_id', $request->delivery_req_id)->where('partner_id', $partner_id)->first();
        if (!$order) return $this->error("No Order Found", 404);
        $this->orderStatusChanger->setDeliveryRequestId($request->delivery_req_id)->setDeliveryStatus($request->delivery_status)->setOrder($order)->updateStatusForIpn();
        return $this->success();
    }

    public function logs(int $partner_id, int $order_id)
    {
        try {
            $logs = $this->orderLogRepository->where('order_id', $order_id)->orderBy('id', 'desc')->get();
            $final_logs = collect();
            foreach ($logs as $log) {
                /** @var OrderObjectRetriever $orderObjectRetriever */
                $orderObjectRetriever = app(OrderObjectRetriever::class);
                $oldOrderObject = $log->old_value ? $orderObjectRetriever->setOrder($log->old_value)->get() : null;
                $newOrderObject = $log->new_value ? $orderObjectRetriever->setOrder($log->new_value)->get() : null;
                /** @var OrderLogGenerator $orderLogGenerator */
                $orderLogGenerator = app(OrderLogGenerator::class);
                $log_details = $orderLogGenerator->setLog($log)->setOldObject($oldOrderObject)->setNewObject($newOrderObject)->getLogDetails();
                if ($log_details) $final_logs->push($log_details);
            }
            return $this->success(ResponseMessages::SUCCESS, ['logs' => $final_logs->toArray()]);
        } catch (Exception $e) {
            return $this->error("Sorry, can't generate logs for this order", 200);
        }
    }

    public function generateLogInvoice(int $partner_id, int $order_id, int $log_id): JsonResponse
    {
        /** @var OrderLog $log */
        $log = $this->orderLogRepository->where('order_id', $order_id)->where('id', $log_id)->first();
        if ($log->invoice) return $this->success(ResponseMessages::SUCCESS, ['link' => $log->invoice]);
        /** @var OrderObjectRetriever $orderObjectRetriever */
        $orderObjectRetriever = app(OrderObjectRetriever::class);
        $newOrderObject = $orderObjectRetriever->setOrder($log->new_value)->get();
        /** @var InvoiceService $invoiceService */
        $invoiceService = app(InvoiceService::class);
        $link = $invoiceService->setOrder($newOrderObject)->generateInvoice();
        $this->orderLogRepository->update($log, ['invoice' => $link]);
        return $this->success(ResponseMessages::SUCCESS, ['link' => $link]);
    }

    public function getAllFilteringOptions(): JsonResponse
    {
        $data['statuses'] = Statuses::get();
        $data['payment_statuses'] = PaymentStatuses::get();
        return $this->success(ResponseMessages::SUCCESS, ['filters' => $data]);
    }

    public function sendEmail($partner, $order)
    {
        $order = Order::where('partner_id', $partner)->find($order);
        if (!$order) return $this->error('Order Not Found', 404);
        if (!$order->customer) return $this->error('Customer Not Found', 404);
        if (!$order->customer->email) return $this->error('Email Not Found', 404);
        dispatch(new OrderEmail($order));
        return $this->success();
    }
}
