<?php namespace App\Http\Resources;

use App\Models\Order;
use App\Services\Order\PriceCalculation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Order $this */
        /** @var PriceCalculation $priceCalculation */
        $priceCalculation = app(PriceCalculation::class);
        return [
            'id' => $this->id,
            'delivery_name' => $this->delivery_name,
            'delivery_address' => $this->delivery_address,
            'delivery_mobile' => $this->delivery_mobile,
            'delivery_vendor' => $this->delivery_vendor ? json_decode($this->delivery_vendor, true) : null,
            'delivery_request_id' => $this->delivery_request_id,
            'delivery_thana' => $this->delivery_thana,
            'delivery_district' => $this->delivery_district,
            'payment_method' => $this->paymentMethod(),
            'due' => $priceCalculation->setOrder($this->resource)->getDue(),
            'weight' => $this->getWeight(),
            'delivery_charge' => (double) $this->delivery_charge,

        ];
    }
}
