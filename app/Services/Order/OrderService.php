<?php


namespace App\Services\Order;


use App\Http\Resources\OrderResource;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkusRepositoryInterface;
use App\Services\BaseService;

class OrderService extends BaseService
{
    protected $orderRepositoryInterface;
    protected $orderSkusRepositoryInterface;

    public function __construct(OrderRepositoryInterface $orderRepositoryInterface, OrderSkusRepositoryInterface $orderSkusRepositoryInterface)
    {
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->orderSkusRepositoryInterface = $orderSkusRepositoryInterface;
    }

    public function getOrderList($partner_id, $request)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $getOrderList = $this->orderRepositoryInterface->getOrderListWithOffsetLimitAndPartner($offset, $limit, $partner_id);
            $orderList = OrderResource::collection($getOrderList);
            if(!$orderList) return $this->error('অর্ডারটি পাওয়া যায় নি ', 404);
            else return $this->success('Success', ['orderList' => $orderList], 200, true);
        } catch(\Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    public function getOrderDetails($partner_id, $order_id)
    {
        try {
            $orderDetails = $this->orderRepositoryInterface->where('partner_id', $partner_id)->find($order_id);
            if(!$orderDetails)
            {
                return $this->error('অর্ডারটি পাওয়া যায় নি', 404);
            }
            $order = $orderDetails;
            $order->items = $orderDetails->items;
            $order = new OrderResource($orderDetails);
            return $this->success('Success', ['order' => $order], 200, true);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    public function delete($partner_id, $order_id)
    {
        $order = $this->orderRepositoryInterface->where('partner_id', $partner_id)->find($order_id);
        if(!$order) return $this->error('অর্ডারটি পাওয়া যায় নি', 404);

        try {
            $OrderSkusIds = $this->orderSkusRepositoryInterface->where('order_id', $order_id)->get(['id']);
            $this->orderSkusRepositoryInterface->whereIn('id', $OrderSkusIds)->delete();
            $order->delete();
            return $this->success('Successful', null, 200, true);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }
}
