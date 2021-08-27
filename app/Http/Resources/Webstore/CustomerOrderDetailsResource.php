<?php namespace App\Http\Resources\Webstore;

use App\Http\Resources\OrderSkuResource;
use App\Services\Order\PriceCalculation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class CustomerOrderDetailsResource extends JsonResource
{
    private $order;
    private array $orderWithProductResource = [];
    /**
     * OrderWithProductResource constructor.
     */
    public function __construct($order)
    {
        $this->order = $order;
        parent::__construct($order);
    }


    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $this->orderWithProductResource = [
            'id'                      => $this->id,
            'partner_wise_order_id'   => $this->partner_wise_order_id,
            'status'                  => $this->status,
            'items'                   => OrderSkuResource::collection($this->items),
            'price'                   => $this->getOrderPriceRelatedInfo(),
        ];
        return $this->orderWithProductResource;
    }

    /**
     * @return array
     */
    private function getOrderPriceRelatedInfo() : array
    {
        /** @var PriceCalculation $price_calculator */
        $price_calculator = (App::make(PriceCalculation::class))->setOrder($this->order);

        return [
            'original_price' => $price_calculator->getOriginalPrice(),
            'discounted_price_without_vat' => $price_calculator->getDiscountedPriceWithoutVat(),
            'promo_discount' => $price_calculator->getPromoDiscount(),
            'order_discount' => $price_calculator->getOrderDiscount(),
            'vat' => $price_calculator->getVat(),
            'delivery_charge' => $price_calculator->getDeliveryCharge(),
            'discounted_price' => $price_calculator->getDiscountedPrice(),
            'paid' => $price_calculator->getPaid(),
            'due' => $price_calculator->getDue(),
        ];
    }


}
