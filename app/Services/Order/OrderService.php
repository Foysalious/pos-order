<?php


namespace App\Services\Order;


use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderWithProductResource;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkusRepositoryInterface;
use App\Services\BaseService;

class OrderService extends BaseService
{
    protected $orderRepositoryInterface;
    protected $orderSkusRepositoryInterface;
    protected $updater;

    public function __construct(OrderRepositoryInterface $orderRepositoryInterface, OrderSkusRepositoryInterface $orderSkusRepositoryInterface, Updater $updater)
    {
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->orderSkusRepositoryInterface = $orderSkusRepositoryInterface;
        $this->updater = $updater;
    }

    public function getOrderList($partner_id, $request)
    {
        list($offset, $limit) = calculatePagination($request);
        $getOrderList = $this->orderRepositoryInterface->getOrderListWithOffsetLimitAndPartner($offset, $limit, $partner_id);
        $orderList = OrderResource::collection($getOrderList);
        if(!$orderList) return $this->error('অর্ডারটি পাওয়া যায় নি ', 404);
        else return $this->success('Success', ['orderList' => $orderList], 200, true);
    }

    public function getOrderDetails($partner_id, $order_id)
    {
            $orderDetails = $this->orderRepositoryInterface->where('partner_id', $partner_id)->find($order_id);
            if(!$orderDetails) return $this->error('অর্ডারটি পাওয়া যায় নি', 404);

            $order = $orderDetails;
            $order->items = $orderDetails->items;
            $order = new OrderWithProductResource($orderDetails);
            return $this->success('Success', ['order' => $order], 200, true);
    }

    public function update($orderUpdateRequest, $partner_id, $order_id)
    {
        $orderDetails = $this->orderRepositoryInterface->where('partner_id', $partner_id)->find($order_id);
        if(!$orderDetails) return $this->error('অর্ডারটি পাওয়া যায় নি', 404);

        $this->updater->setPartnerId($partner_id)
            ->setOrderId($order_id)
            ->setOrder($orderDetails)
            ->setCustomerId($orderUpdateRequest->customer_id)
            ->setStatus($orderUpdateRequest->status)
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

    public function checkOrderExists($order_id)
    {
        $orderDetails = $this->orderRepositoryInterface->find($order_id);
        if(!$orderDetails) return $this->error('অর্ডারটি পাওয়া যায় নি', 404);
        return $this->success('Success', ['order_id' => $orderDetails->id], 200, true);
    }
}
