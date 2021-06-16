<?php namespace App\Services\Order;

use App\Models\Order;
use App\Models\OrderSku;
use Illuminate\Database\Eloquent\Relations\HasMany;
use function App\Helper\Formatters\formatTakaToDecimal;

class PriceCalculation
{
    private Order $order;
    private float $totalPrice;
    private float $totalVat;
    private float $totalItemDiscount;
    private float $totalBill;
    private float $paid;
    private float $due;
    private float $totalDiscount;
    private float $appliedDiscount;
    private float $originalTotal;
    private float $netBill;

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
        $this->calculateThisItems();
        $this->totalDiscount = $this->totalItemDiscount + $this->discountsAmountWithoutProduct();
        $this->appliedDiscount = ($this->discountsAmountWithoutProduct() > $this->totalBill) ? $this->totalBill : $this->discountsAmountWithoutProduct();
        $this->originalTotal = round($this->totalBill - $this->appliedDiscount, 2);
        $this->netBill = $this->originalTotal + round((double)$this->order->interest, 2) + (double)round($this->order->bank_transaction_charge, 2);
        $this->netBill += round($this->order->delivery_charge, 2);
        $this->calculatePaidAmount();
        $this->paid = round($this->paid ?: 0, 2);
        $this->due = ($this->netBill - $this->paid) > 0 ? ($this->netBill - $this->paid) : 0;
        $this->formatAllToTaka();
    }

    /**
     * @return float
     */
    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    /**
     * @return float|int
     */
    public function getNetBill(): float|int
    {
        return $this->netBill;
    }

    /**
     * @return float
     */
    public function getTotalBill(): float
    {
        return $this->totalBill;
    }

    /**
     * @return float
     */
    public function getTotalVat(): float
    {
        return $this->totalVat;
    }

    /**
     * @return float
     */
    public function getDue(): float
    {
        return $this->due;
    }

    /**
     * @return float
     */
    public function getPaid(): float
    {
        return $this->paid;
    }

    /**
     * @return float|int
     */
    public function getTotalItemDiscount(): float|int
    {
        return $this->totalItemDiscount;
    }

    /**
     * @return float
     */
    public function getTotalDiscount(): float
    {
        return $this->totalDiscount;
    }

    private function initializeTotalsToZero()
    {
        $this->totalPrice = 0;
        $this->totalVat = 0;
        $this->totalItemDiscount = 0;
        $this->totalBill = 0;
    }

    private function calculateThisItems(): void
    {
        $this->initializeTotalsToZero();
        foreach ($this->order->orderSkus as $order_sku) {
            /** @var OrderSku $order_sku */
            $order_sku = $order_sku->calculate();
            $this->updateTotalPriceAndCost($order_sku);
        }
    }

    private function updateTotalPriceAndCost(OrderSku $orderSku)
    {
        $this->totalPrice += $orderSku->getPrice();
        $this->totalVat += $orderSku->getVat();
        $this->totalItemDiscount += $orderSku->getDiscountAmount();
        $this->totalBill += $orderSku->getTotal();
    }

    private function discountsAmountWithoutProduct()
    {
        return $this->discountsWithoutProduct()->sum('amount');
    }

    /**
     * @return HasMany
     */
    public function discountsWithoutProduct(): HasMany
    {
        return $this->order->discounts()->whereNull('item_id');
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
        $this->totalPrice = formatTakaToDecimal($this->totalPrice);
        $this->totalVat = formatTakaToDecimal($this->totalVat);
        $this->totalItemDiscount = formatTakaToDecimal($this->totalItemDiscount);
        $this->totalBill = formatTakaToDecimal($this->totalBill);
        $this->due = formatTakaToDecimal($this->due);
    }

}
