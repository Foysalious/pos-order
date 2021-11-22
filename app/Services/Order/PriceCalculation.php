<?php namespace App\Services\Order;

use App\Models\Order;
use App\Models\OrderSku;
use App\Services\APIServerClient\ApiServerClient;
use App\Services\Delivery\Methods;
use App\Services\Discount\Constants\DiscountTypes;

class PriceCalculation
{
    private Order $order;
    private float $originalPrice;
    private float $vat;
    private float $productDiscount;
    private float $productDiscountedPrice;
    private float $paid;
    private float $due;
    private float $discount;
    private float $discountedPrice;
    private float $discountedPriceWithoutVat;
    /**
     * @var int|mixed
     */
    private mixed $orderDiscount;
    /**
     * @var int|mixed
     */
    private mixed $promoDiscount;

    private string $deliveryMethod;

    /**
     * @param Order $order
     * @return PriceCalculation
     */
    public function setOrder(Order $order): PriceCalculation
    {
        $this->order = $order;
        $this->calculate();
        return $this;
    }

    private function calculate()
    {
        $this->calculateOrderSkus();
        $this->orderDiscount = $this->orderDiscount();
        $this->promoDiscount = $this->promoDiscount();
        $orderAndVoucherDiscount = $this->orderDiscount + $this->promoDiscount;
        $this->discount = $this->productDiscount + $orderAndVoucherDiscount;
        $appliedOrderAndVoucherDiscount = ($orderAndVoucherDiscount > $this->productDiscountedPrice) ? $this->productDiscountedPrice : $orderAndVoucherDiscount;
        $originalTotal = round($this->productDiscountedPrice - $appliedOrderAndVoucherDiscount, 2);
        $this->discountedPrice = $originalTotal + round((double)$this->order->interest, 2) + (double)round($this->order->bank_transaction_charge, 2);
        $this->discountedPrice += round($this->order->delivery_charge, 2);
        $this->calculatePaidAmount();
        $this->paid = round($this->paid ?: 0, 2);
        $this->due = ($this->discountedPrice - $this->paid) > 0 ? ($this->discountedPrice - $this->paid) : 0;
        $this->formatAllToTaka();
    }

    /**
     * Total products/skus price (without vat and discount)
     * @return float
     */
    public function getOriginalPrice(): float
    {
        return $this->originalPrice;
    }

    /**
     * Total product/sku discounted price without VAT
     * @return float
     */
    public function getDiscountedPriceWithoutVat(): float
    {
        return $this->discountedPriceWithoutVat;
    }

    /**
     * Total price the customer will pay
     * @return float
     */
    public function getDiscountedPrice(): float
    {
        return $this->discountedPrice;
    }

    /**
     * Product discounted price (without order and voucher discount, bank transaction charge, interest and delivery charge)
     * @return float
     */
    public function getProductDiscountedPrice(): float
    {
        return $this->productDiscountedPrice;
    }

    /**
     * Promo Discount
     * @return float
     */
    public function getPromoDiscount(): float
    {
        return $this->promoDiscount;
    }

    /**
     * Discount on Order
     * @return float
     */
    public function getOrderDiscount(): float
    {
        return $this->orderDiscount;
    }

    /**
     * Total VAT of the order
     * @return float
     */
    public function getVat(): float
    {
        return $this->vat;
    }

    /**
     * Total due of the order
     * @return float
     */
    public function getDue(): float
    {
        return (double) $this->due;
    }

    /**
     * Total paid amount of the order
     * @return float
     */
    public function getPaid(): float
    {
        return $this->paid;
    }

    /**
     * Total discounts on products/skus
     * @return float|int
     */
    public function getProductDiscount(): float|int
    {
        return $this->productDiscount;
    }

    /**
     * Total discount of the order (product discount + order discount + voucher discount)
     * @return float
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * Delivery Charge of the order
     * @return float
     */
    public function getDeliveryCharge(): float
    {
        return (double) $this->order->delivery_charge;
    }

    private function initializeTotalsToZero()
    {
        $this->originalPrice = 0;
        $this->vat = 0;
        $this->productDiscount = 0;
        $this->discountedPriceWithoutVat = 0;
        $this->productDiscountedPrice = 0;
    }

    private function calculateOrderSkus(): void
    {
        $this->initializeTotalsToZero();
        /** @var OrderSku $order_sku */
        foreach ($this->order->orderSkus as $order_sku) {
            $order_sku = $order_sku->calculate();
            $this->updateTotalPriceAndCost($order_sku);
        }
    }

    private function updateTotalPriceAndCost(OrderSku $orderSku)
    {
        $this->originalPrice += $orderSku->getOriginalPrice();
        $this->vat += $orderSku->getVat();
        $this->productDiscount += $orderSku->getDiscountAmount();
        $this->discountedPriceWithoutVat += $orderSku->discountedPriceWithoutVat();
        $this->productDiscountedPrice += $orderSku->getDiscountedPrice();
    }

    private function orderDiscount()
    {
        return $this->order->discounts->where('type', DiscountTypes::ORDER)->sum('amount');
    }

    private function promoDiscount()
    {
        return $this->order->discounts->where('type', DiscountTypes::VOUCHER)->sum('amount');
    }

    private function creditPaymentsCollect()
    {
        return $this->order->payments->filter(function ($payment) {
            return $payment->transaction_type === 'credit';
        });
    }

    private function debitPaymentsCollect()
    {
        return $this->order->payments->filter(function ($payment) {
            return $payment->transaction_type === 'debit';
        });
    }

    private function calculatePaidAmount()
    {
        $credit = $this->creditPaymentsCollect()->sum('amount');
        $debit = $this->debitPaymentsCollect()->sum('amount');
        $this->paid = $credit - $debit;
    }

    private function formatAllToTaka(): void
    {
        $this->originalPrice = formatTakaToDecimal($this->originalPrice);
        $this->discountedPriceWithoutVat = formatTakaToDecimal($this->discountedPriceWithoutVat);
        $this->orderDiscount = formatTakaToDecimal($this->orderDiscount);
        $this->promoDiscount = formatTakaToDecimal($this->promoDiscount);
        $this->vat = formatTakaToDecimal($this->vat);
        $this->paid = formatTakaToDecimal($this->paid);
        $this->due = formatTakaToDecimal($this->due);
    }

    /**
     * @param mixed $deliveryMethod
     * @return PriceCalculation
     */
    public function setDeliveryMethod(string $deliveryMethod)
    {
        $this->deliveryMethod = $deliveryMethod;
        return $this;
    }



}
