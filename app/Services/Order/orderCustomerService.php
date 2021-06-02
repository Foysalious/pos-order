<?php namespace App\Services\Order;


use App\Exceptions\OrderException;
use App\Interfaces\OrderPaymentRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Services\BaseService;
use App\Services\Order\Constants\OrderLogTypes;

class orderCustomerService extends BaseService
{
    protected $orderRepository, $orderPaymentRepository, $orderUpdater;
    protected $order;

    public function __construct(OrderRepositoryInterface $orderRepository,
                                OrderPaymentRepositoryInterface $orderPaymentRepository,
                                Updater $orderUpdater)
    {
        $this->orderRepository = $orderRepository;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->orderUpdater = $orderUpdater;
    }

    public function update($customer_id, $partner_id, $order_id)
    {
        $this->order = $this->orderRepository->where('partner_id', $partner_id)->find($order_id);
        if(!$this->order) return $this->error(trans('order.order_not_found'), 404);
        if($this->order->customer->id != $customer_id) return $this->error(trans('order.customer_not_found'), 404);
        $this->checkCustomerPayment($customer_id, $order_id);
        return $this->success('Successful', null, 200);
    }

    private function checkCustomerPayment($customer_id, $order_id)
    {
        $orderPaymentStatus = $this->orderPaymentRepository->where('order_id', $order_id)->get();
        if(count($orderPaymentStatus) > 0) throw new OrderException(trans('order.update.no_customer_update'));
        $this->orderUpdater->setOrderId($order_id)
            ->setOrder($this->order)
            ->setCustomerId($customer_id)
            ->setOrderLogType(OrderLogTypes::CUSTOMER)
            ->update();
    }
}
