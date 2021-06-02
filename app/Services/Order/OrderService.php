<?php namespace App\Services\Order;

use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderWithProductResource;
use App\Interfaces\OrderLogRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkusRepositoryInterface;
use App\Interfaces\PaymentLinkRepositoryInterface;
use App\Models\Order;
use App\Services\BaseService;

class OrderService extends BaseService
{
    protected $orderRepositoryInterface;
    protected $orderSkusRepositoryInterface;
    protected $updater, $orderSearch, $orderFilter;
    protected $paymentLinkRepository;
    public OrderLogRepositoryInterface $orderLogRepository;

    public function __construct(OrderRepositoryInterface $orderRepositoryInterface,
                                OrderSkusRepositoryInterface $orderSkusRepositoryInterface,
                                OrderSearch $orderSearch,
                                OrderFilter $orderFilter,
                                Updater $updater,
                                PaymentLinkRepositoryInterface $paymentLinkRepository,
                                OrderLogRepositoryInterface $orderLogRepository
    )
    {
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->orderSkusRepositoryInterface = $orderSkusRepositoryInterface;
        $this->updater = $updater;
        $this->orderSearch = $orderSearch;
        $this->orderFilter = $orderFilter;
        $this->paymentLinkRepository = $paymentLinkRepository;
        $this->orderLogRepository = $orderLogRepository;
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
        if(!$orderList) return $this->error("You're not authorized to access this order", 403);
        else return $this->success('Success', ['orders' => $orderList], 200, true);
    }

    public function getOrderDetails($partner_id, $order_id)
    {
        $order = $this->orderRepositoryInterface->where('partner_id', $partner_id)->find($order_id);
        if(!$order) return $this->error("You're not authorized to access this order", 403);
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
        if(!$orderDetails) return $this->error("You're not authorized to access this order", 403);
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
        if(!$order) return $this->error("You're not authorized to access this order", 403);
        $OrderSkusIds = $this->orderSkusRepositoryInterface->where('order_id', $order_id)->get(['id']);
        $this->orderSkusRepositoryInterface->whereIn('id', $OrderSkusIds)->delete();
        $order->delete();
        return $this->success('Successful', null, 200, true);
    }

    public function getOrderWithChannel($order_id)
    {
        $orderDetails = $this->orderRepositoryInterface->find($order_id);
        if(!$orderDetails) return $this->error("You're not authorized to access this order", 403);
        $order = [
            'id' => $orderDetails->id,
            'sales_channel' => $orderDetails->sales_channel_id == 1 ? 'pos' : 'webstore'
        ];
        return $this->success('Success', ['order' => $order], 200, true);
    }

    public function getOrderPaymentLink(Order $order) {
        $payment_link_target = $order->getPaymentLinkTarget();
        $payment_link = $this->paymentLinkRepository->getActivePaymentLinkByPosOrder($payment_link_target);
        return isset($payment_link) ? $payment_link : false;
    }
}
