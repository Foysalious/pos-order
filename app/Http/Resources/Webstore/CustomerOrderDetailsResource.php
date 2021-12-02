<?php namespace App\Http\Resources\Webstore;

use App\Http\Resources\OrderSkuResource;
use App\Services\APIServerClient\ApiServerClient;
use App\Services\Order\PriceCalculation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class CustomerOrderDetailsResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        list($is_registered_for_sdelivery,$delivery_method) = $this->getDeliveryInformation($this->partner->id);
        return [
            'id'                      => $this->id,
            'partner_wise_order_id'   => $this->partner_wise_order_id,
            'status'                  => $this->status,
            'items'                   => OrderSkuResource::collection($this->items),
            'price'                   => $this->getOrderPriceRelatedInfo(),
            'is_registered_for_sdelivery' => $is_registered_for_sdelivery,
            'delivery_method' => $delivery_method
        ];
    }

    /**
     * @return array
     */
    private function getOrderPriceRelatedInfo() : array
    {
        /** @var PriceCalculation $price_calculator */
        $price_calculator = (App::make(PriceCalculation::class))->setOrder($this->resource);

        return [
            'original_price' => $price_calculator->getOriginalPrice(),
            'discounted_price_without_vat' => $price_calculator->getDiscountedPriceWithoutVat(),
            'product_discount' => $price_calculator->getProductDiscount(),
            'promo_discount' => $price_calculator->getPromoDiscount(),
            'order_discount' => $price_calculator->getOrderDiscount(),
            'vat' => $price_calculator->getVat(),
            'delivery_charge' => $price_calculator->getDeliveryCharge(),
            'discounted_price' => $price_calculator->getDiscountedPrice(),
            'paid' => $price_calculator->getPaid(),
            'due' => $price_calculator->getDue(),
        ];
    }

    private function getDeliveryInformation($partnerId)
    {
        /** @var ApiServerClient $apiServerClient */
        $apiServerClient = app(ApiServerClient::class);
        $partnerInfo =  $apiServerClient->get('pos/v1/partners/'. $partnerId)['partner'];
        return [$partnerInfo['is_registered_for_sdelivery'],$partnerInfo['delivery_method']];
    }


}
