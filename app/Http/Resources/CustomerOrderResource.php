<?php namespace App\Http\Resources;

use App\Models\Order;
use App\Services\Order\PriceCalculation;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Order $this */
        /** @var PriceCalculation $priceCalculation */
        $priceCalculation = app(PriceCalculation::class);

        return [
            'id' => $this->id,
            'status' => $this->status,
            'date' => $this->created_at->format('d,M,Y'),
            'price' => $priceCalculation->setOrder($this->resource)->getTotalBill(),
        ];
    }
}
