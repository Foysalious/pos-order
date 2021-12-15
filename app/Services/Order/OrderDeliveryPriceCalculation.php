<?php namespace App\Services\Order;

use App\Models\Order;
use App\Services\APIServerClient\ApiServerClient;
use App\Services\Delivery\Methods;
use App\Services\Order\Constants\SalesChannelIds;
use Illuminate\Support\Facades\App;

class OrderDeliveryPriceCalculation
{
    private Order $order;

    /**
     * @param Order $order
     * @return OrderDeliveryPriceCalculation
     */
    public function setOrder(Order $order): OrderDeliveryPriceCalculation
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



    public function calculateDeliveryCharge()
    {
        if ($this->order->sales_channel_id != SalesChannelIds::WEBSTORE)
            return null;

        if ($this->order->getDeliveryVendor() == Methods::OWN_DELIVERY) return $this->order->partner->delivery_charge;
        $data = [
            'weight' => $this->order->getWeight(),
            'delivery_district' => $this->order->delivery_district,
            'delivery_thana' => $this->order->delivery_thana,
            'partner_id' => $this->order->partner->id,
            'cod_amount' => $this->getDue()
        ];
        /** @var ApiServerClient $apiServerClient */
        $apiServerClient = app(ApiServerClient::class);
        return  $apiServerClient->post('v2/pos/delivery/delivery-charge', $data)['delivery_charge'];

    }
}
