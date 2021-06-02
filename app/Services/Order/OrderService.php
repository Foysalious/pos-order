<?php namespace App\Services\Order;

use App\Http\Requests\OrderCreateRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderWithProductResource;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkusRepositoryInterface;
use App\Interfaces\PaymentLinkRepositoryInterface;
use App\Models\Order;
use App\Models\Partner;
use App\Services\BaseService;
use App\Services\PaymentLink\Creator as PaymentLinkCreator;
use App\Services\PaymentLink\PaymentLinkTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class OrderService extends BaseService
{
    protected $orderRepositoryInterface;
    protected $orderSkusRepositoryInterface;
    protected $updater, $orderSearch, $orderFilter;
    protected $paymentLinkRepository;
    /** @var Creator */
    private Creator $creator;
    /** @var PaymentLinkCreator */
    private PaymentLinkCreator $paymentLinkCreator;

    public function __construct(OrderRepositoryInterface $orderRepositoryInterface,
                                OrderSkusRepositoryInterface $orderSkusRepositoryInterface,
                                OrderSearch $orderSearch,
                                OrderFilter $orderFilter,
                                Updater $updater,
                                PaymentLinkRepositoryInterface $paymentLinkRepository,
                                Creator $creator,
                                PaymentLinkCreator $paymentLinkCreator
    )
    {
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->orderSkusRepositoryInterface = $orderSkusRepositoryInterface;
        $this->updater = $updater;
        $this->orderSearch = $orderSearch;
        $this->orderFilter = $orderFilter;
        $this->paymentLinkRepository = $paymentLinkRepository;
        $this->creator = $creator;
        $this->paymentLinkCreator = $paymentLinkCreator;
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

        $ordersList = $this->orderRepositoryInterface->getOrderListWithPagination($offset, $limit, $partner_id, $orderSearch, $orderFilter);
        $orderList = OrderResource::collection($ordersList);
        if(!$orderList) return $this->error('অর্ডারটি পাওয়া যায় নি', 404);
        else return $this->success('Success', ['orders' => $orderList], 200, true);
    }

    /**
     * @param $partner
     * @param OrderCreateRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store($partner, OrderCreateRequest $request)
    {
        $skus = is_array($request->skus) ?: json_decode($request->skus);
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
            ->setIsDiscountPercentage($request->is_discount_percentage)
            ->setPaidAmount($request->paid_amount)
            ->setPaymentMethod($request->payment_method)
            ->create();
        $payment_link_amount = $request->has('payment_link_amount') ? $request->payment_link_amount : $order->net_bill;
        if ($request->payment_method == 'payment_link') $payment_link = $this->createPaymentLink($payment_link_amount, $partner, $order);
        return $this->success('Successful', ['order' => ['id' => $order->id], 'payment' => $payment_link ?? null]);
    }

    public function getOrderDetails($partner_id, $order_id)
    {
        $order = $this->orderRepositoryInterface->where('partner_id', $partner_id)->find($order_id);
        if(!$order) return $this->error('অর্ডারটি পাওয়া যায় নি', 404);
        $order->calculate();
        $resource = new OrderWithProductResource($order);
        if($order->due > 0){
            $paymentLink = $this->getOrderPaymentLink($order);
            if ($paymentLink)
                $resource = $resource->getOrderDetailsWithPaymentLink($paymentLink);
        }
        return $this->success('Success', ['order' => $resource], 200, true);
    }

    public function update($orderUpdateRequest, $partner_id, $order_id)
    {
        $orderDetails = $this->orderRepositoryInterface->where('partner_id', $partner_id)->find($order_id);
        if(!$orderDetails) return $this->error('অর্ডারটি পাওয়া যায় নি', 404);
        $this->updater->setPartnerId($partner_id)
            ->setOrderId($order_id)
            ->setOrder($orderDetails)
            ->setCustomerId($orderUpdateRequest->customer_id)
            ->setSalesChannelId($orderUpdateRequest->sales_channel_id)
            ->setUpdatedSkus($orderUpdateRequest->skus)
            ->setEmiMonth($orderUpdateRequest->emi_month)
            ->setInterest($orderUpdateRequest->interest)
            ->setDeliveryCharge($orderUpdateRequest->delivery_charge)
            ->setBankTransactionCharge($orderUpdateRequest->bank_transaction_charge)
            ->setDeliveryName($orderUpdateRequest->delivery_name)
            ->setDeliveryMobile($orderUpdateRequest->delivery_mobile)
            ->setDeliveryAddress($orderUpdateRequest->delivery_address)
            ->setNote($orderUpdateRequest->note)
            ->setVoucherId($orderUpdateRequest->voucher_id)
            ->update();

        return $this->success('Successful', null, 200, true);
    }

    public function delete($partner_id, $order_id)
    {
        $order = $this->orderRepositoryInterface->where('partner_id', $partner_id)->find($order_id);
        if(!$order) return $this->error('অর্ডারটি পাওয়া যায় নি', 404);

        $OrderSkusIds = $this->orderSkusRepositoryInterface->where('order_id', $order_id)->get(['id']);
        $this->orderSkusRepositoryInterface->whereIn('id', $OrderSkusIds)->delete();
        $order->delete();
        return $this->success('Successful', null, 200, true);
    }

    public function getOrderWithChannel($order_id)
    {
        $orderDetails = $this->orderRepositoryInterface->find($order_id);
        if(!$orderDetails) return $this->error('অর্ডারটি পাওয়া যায় নি', 404);
        $order = [
            'id' => $orderDetails->id,
            'sales_channel' => $orderDetails->sales_channel_id == 1 ? 'pos' : 'webstore'
        ];
        return $this->success('Success', ['order' => $order], 200, true);
    }

    public function getOrderPaymentLink(Order $order) {
        $payment_link_target = $order->getPaymentLinkTarget();
        $payment_link = $this->paymentLinkRepository->getActivePaymentLinkByPosOrder($payment_link_target);
        if ($payment_link) {
            return $payment_link;
        } else
            return false;
    }

    private function createPaymentLink($payment_link_amount, $partner, $order)
    {
        if (!$partner instanceof Partner) $partner = Partner::find($partner);
        $paymentLink = $this->paymentLinkCreator->setAmount($payment_link_amount)->setReason("PosOrder ID: $order->id Due payment")
            ->setUserName($partner->name)->setUserId($partner->id)
            ->setUserType('partner')
            ->setTargetId($order->id)
            ->setTargetType('pos_order');
        if ($order->customer_id) $paymentLink->setPayerId($order->customer_id)->setPayerType('pos_customer');
        $paymentLink = $paymentLink->create();
        $transformer = new PaymentLinkTransformer();
        $transformer->setResponse($paymentLink);
        return ['link' => config('pos.payment_link_web_url') . '/' . $transformer->getLinkIdentifier()];
    }
}
