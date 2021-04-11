<?php namespace App\Services\Order;

use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkuRepositoryInterface;
use App\Interfaces\PartnerRepositoryInterface;
use App\Models\Order;
use App\Models\Partner;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Constants\SalesChannels;
use App\Services\Order\Validators\OrderCreateValidator;
use App\Services\Order\Payment\Creator as PaymentCreator;

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
    /**
     * @var InventoryServerClient
     */
    private $client;
    /**
     * @var mixed
     */
    private $skus;
    /**
     * @var \Illuminate\Support\Collection
     */
    private $sku_details;
    /**
     * @var Order
     */
    private $order;
    /**
     * @var OrderSkuRepositoryInterface
     */
    private $orderSkuRepository;

    /**
     * @var PaymentCreator
     */
    private $paymentCreator;


    public function __construct(OrderCreateValidator $createValidator,
                                OrderRepositoryInterface $orderRepositoryInterface, PartnerRepositoryInterface
                                $partnerRepositoryInterface,InventoryServerClient $client,
                                OrderSkuRepositoryInterface $orderSkuRepository,PaymentCreator $paymentCreator)
    {
        $this->createValidator = $createValidator;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->partnerRepositoryInterface = $partnerRepositoryInterface;
        $this->orderSkuRepository = $orderSkuRepository;
        $this->paymentCreator =$paymentCreator;
        $this->client = $client;
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
        $order_data['delivery_charge']       = isset($this->data['delivery_charge']) && $this->data['delivery_charge'] == SalesChannels::WEBSTORE ? $this->partner->delivery_charge : 0;
        $order_data['status']                = isset($this->data['status']) && $this->data['status'] ? : 'Pending';
        $this->order                               = $this->orderRepositoryInterface->create($order_data);
        $this->createOrderSkus();
        $this->order->calculate();
        return $this->success('Successful', ['order' => $this->order], 200);
    }

    public function createOrderSkus()
    {
        $sku_ids = array_column(json_decode($this->data['skus']),'id');
        $this->sku_details   = collect($this->getSkuDetails($sku_ids))->keyBy('id')->toArray();
        //dd($this->sku_details);
        $skus = json_decode($this->data['skus']);
        foreach($skus as $sku)
        {
            $order_sku['order_id'] = $this->order->id;
            $order_sku['name'] = $this->sku_details[$sku->id]['name'];
            $order_sku['sku_id'] = $sku->id;
            $order_sku['details'] = null;
            $order_sku['quantity'] = $sku->quantity;
            $order_sku['unit_price'] =  $this->sku_details[$sku->id]['sku_channel'][0]['price'];
            $order_sku['unit'] = null;
            $order_sku['warranty'] = $this->sku_details[$sku->id]['warranty'];
            $order_sku['warranty_unit'] = $this->sku_details[$sku->id]['warranty_unit'];
            $order_sku['vat_percentage'] = $this->sku_details[$sku->id]['vat_percentage'];
            $this->orderSkuRepository->create($order_sku);
        }
        if (isset($this->data['paid_amount']) && $this->data['paid_amount'] > 0) {
            $payment_data['pos_order_id'] = $this->order->id;
            $payment_data['amount']       = $this->data['paid_amount'];
            $payment_data['method']       = $this->data['payment_method'] ?: 'cod';
            $this->paymentCreator->credit($payment_data);
        }

    }

    private function getSkuDetails($sku_ids)
    {
        $url = 'api/v1/partners/'.$this->partner->id.'/skus?skus='.json_encode($sku_ids).'&channel_id=1';
        $response =  $this->client->get($url);
        return $response['skus'];
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
