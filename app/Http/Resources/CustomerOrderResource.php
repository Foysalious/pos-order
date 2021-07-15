<?php namespace App\Http\Resources;

use App\Models\Order;
use App\Services\Order\PriceCalculation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

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
        /** @var PriceCalculation $price_calculator */
        $price_calculator = (App::make(PriceCalculation::class))->setOrder($this->resource);

        return [
            'id' => $this->id,
            'status' => $this->status,
            'date' => $this->created_at->format('d,M,Y'),
            'discounted_price' => $price_calculator->getDiscountedPrice(),
        ];
    }
}
