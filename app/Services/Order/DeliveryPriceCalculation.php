<?php namespace App\Services\Order;

use App\Models\Order;
use App\Services\APIServerClient\ApiServerClient;
use App\Services\Delivery\Methods;
use Illuminate\Support\Facades\App;

class DeliveryPriceCalculation
{

    private $deliveryMethod;
    private Order $order;



    /**
     * @param mixed $deliveryMethod
     * @return DeliveryPriceCalculation
     */
    public function setDeliveryMethod(string $deliveryMethod)
    {
        $this->deliveryMethod = $deliveryMethod;
        return $this;
    }

    /**
     * @param Order $order
     * @return DeliveryPriceCalculation
     */
    public function setOrder(Order $order): DeliveryPriceCalculation
    {
        $this->order = $order;
        return $this;
    }

    private function getDue(): float
    {
        /** @var PriceCalculation $order_bill */
        $order_bill = App::make(PriceCalculation::class);
        $order_bill = $order_bill->setOrder($this->order);
        return $order_bill->getDue();
    }

    private function getDeliveryMethod()
    {
        /** @var ApiServerClient $apiServerClient */
        $apiServerClient = app(ApiServerClient::class);
        return $apiServerClient->get('pos/v1/partners/'. $this->order->partner->id)['partner']['delivery_method'];
    }

    public function calculateDeliveryCharge(): bool
    {
        $this->setDeliveryMethod($this->getDeliveryMethod());

        if(!$this->order->delivery_district || !$this->order->delivery_thana)
            return false;

        if ($this->deliveryMethod == Methods::OWN_DELIVERY )
            return $this->order->delivery_charge;
        $data = [
                'weight' => $this->order->getWeight(),
                'delivery_district' => $this->order->delivery_district,
                'delivery_thana' => $this->order->delivery_thana,
                'partner_id' => $this->order->partner->id,
                'cod_amount' => $this->getDue()
            ];
            /** @var ApiServerClient $apiServerClient */
            $apiServerClient = app(ApiServerClient::class);
            return $apiServerClient->post('v2/pos/delivery/delivery-charge', $data)['delivery_charge'];
    }
}
