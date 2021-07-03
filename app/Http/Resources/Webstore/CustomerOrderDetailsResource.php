<?php namespace App\Http\Resources\Webstore;
use App\Http\Resources\OrderSkuResource;
use App\Repositories\PaymentLinkRepository;
use App\Services\Order\PriceCalculation;
use App\Services\PaymentLink\PaymentLinkTransformer;
use App\Services\Transaction\Constants\TransactionTypes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
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
            'delivery_charge'   => $this->delivery_charge,
            'promo'             => $this->getVoucher()->pluck('amount')->first(),
            'total_price' => $price_calculator->getTotalPrice(),
            'total_bill' => $price_calculator->getTotalBill(),
            'discount_amount' => $price_calculator->getTotalDiscount(),
            'due_amount' => $price_calculator->getDue(),
            'paid_amount' => $price_calculator->getPaid(),
            'total_item_discount' => $price_calculator->getTotalItemDiscount(),
            'total_vat' => $price_calculator->getTotalVat(),
        ];
    }


}
