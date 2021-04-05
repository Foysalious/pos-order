<?php namespace App\Services\Order;

use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\PartnerRepositoryInterface;
use App\Models\Partner;
use App\Services\Order\Constants\SalesChannels;
use App\Services\Order\Validators\OrderCreateValidator;
use App\Traits\ResponseAPI;

class Creator
{
    use ResponseAPI;
    private $createValidator;
    private $partner;
    private $address;
    /**
     * @var array
     */
    private $data;
    private $status;
    private $orderRepositoryInterface;
    /**
     * @var PartnerRepositoryInterface
     */
    private $partnerRepositoryInterface;

    public function __construct(OrderCreateValidator $createValidator, OrderRepositoryInterface $orderRepositoryInterface, PartnerRepositoryInterface $partnerRepositoryInterface)
    {
        $this->createValidator = $createValidator;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->partnerRepositoryInterface = $partnerRepositoryInterface;
    }

    public function setPartner( $partner)
    {
       $partner =  Partner::find($partner);
       $this->partner = $partner;
        return $this;
    }

    public function setData(array $data)
    {
        $this->data = $data;
       // $this->createValidator->setProducts(json_decode($this->data['services'], true));
        if (!isset($this->data['payment_method'])) $this->data['payment_method'] = 'cod';
        if (isset($this->data['customer_address'])) $this->setAddress($this->data['customer_address']);
        return $this;
    }

    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }


    public function create()
    {
        $order_data['partner_id']            = $this->partner->id;
        $order_data['customer_id']           = $this->resolveCustomerId();
        $order_data['address']               = $this->address;
        $order_data['previous_order_id']     = (isset($this->data['previous_order_id']) && $this->data['previous_order_id']) ? $this->data['previous_order_id'] : null;
        $order_data['partner_wise_order_id'] = $this->createPartnerWiseOrderId($this->partner);
        $order_data['emi_month']             = isset($this->data['emi_month']) ? $this->data['emi_month'] : null;
        $order_data['sales_channel']         = isset($this->data['sales_channel']) ? $this->data['sales_channel'] : SalesChannels::POS;
        $order_data['delivery_charge']       = isset($this->data['sales_channel']) && $this->data['sales_channel'] == SalesChannels::WEBSTORE ? $this->partner->delivery_charge : 0;
        $order_data['status']                = isset($this->data['status']) && $this->data['status'] ? : 'Pending';
        $order                               = $this->orderRepositoryInterface->create($order_data);
        return $this->success('Successful', ['order' => $order], 200);
    }

    private function resolveCustomerId()
    {
        return $this->data['customer_id'];
    }

    private function createPartnerWiseOrderId(Partner $partner)
    {
        $lastOrder    = $partner->orders()->orderBy('id', 'desc')->first();
        $lastOrder_id = $lastOrder ? $lastOrder->partner_wise_order_id : 0;
        return $lastOrder_id + 1;
    }



}
