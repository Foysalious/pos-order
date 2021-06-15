<?php namespace App\Services\Order;

use App\Models\Order;
use App\Models\OrderSku;
use function App\Helper\Formatters\formatTakaToDecimal;

class PriceCalculation
{
    private Order $order;
    private $totalPrice;
    private $totalVat;
    private $totalItemDiscount;
    private $totalBill;
    private $paid;
    private $due;
    private $discountAmount;
    /**
     * @var int|mixed
     */
    private $totalDiscount;
    /**
     * @var int|mixed
     */
    private $appliedDiscount;
    private float $originalTotal;
    private float $netBill;
    private bool $isCalculated;

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
        $this->_calculateThisItems();
        $this->totalDiscount = $this->totalItemDiscount + $this->discountsAmountWithoutProduct();
        $this->appliedDiscount = ($this->discountsAmountWithoutProduct() > $this->totalBill) ? $this->totalBill : $this->discountsAmountWithoutProduct();
        $this->originalTotal = round($this->totalBill - $this->appliedDiscount, 2);
        $this->netBill = $this->originalTotal + round((double)$this->order->interest, 2) + (double)round($this->order->bank_transaction_charge, 2);
        $this->netBill += round($this->order->delivery_charge, 2);
        $this->_calculatePaidAmount();
        $this->paid = round($this->paid ?: 0, 2);
        $this->due = ($this->netBill - $this->paid) > 0 ? ($this->netBill - $this->paid) : 0;
        $this->isCalculated = true;
        $this->_formatAllToTaka();
    }

    /**
     * @return float|int
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * @return float|int|number
     */
    public function getTotalBill()
    {
        return $this->totalBill;
    }

    /**
     * @return number
     */
    public function getTotalVat()
    {
        return $this->totalVat;
    }

    public function getDue()
    {
        return (double) $this->due;
    }

    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * @return float|int
     */
    public function getTotalItemDiscount()
    {
        return $this->totalItemDiscount;
    }

    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    private function _initializeTotalsToZero()
    {
        $this->totalPrice = 0;
        $this->totalVat = 0;
        $this->totalItemDiscount = 0;
        $this->totalBill = 0;
    }

    private function _calculateThisItems()
    {
        $this->_initializeTotalsToZero();
        foreach ($this->order->orderSkus as $order_sku) {
            /** @var OrderSku $order_sku */
            $order_sku = $order_sku->calculate();
            $this->_updateTotalPriceAndCost($order_sku);
        }
        return $this;
    }

    private function _updateTotalPriceAndCost(OrderSku $orderSku)
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

    public function discountsWithoutProduct()
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

    private function _calculatePaidAmount()
    {
        $credit = $this->creditPaymentsCollect()->sum('amount');
        $debit = $this->debitPaymentsCollect()->sum('amount');
        $this->paid = $credit - $debit;
    }

    private function _formatAllToTaka()
    {
        $this->totalPrice = formatTakaToDecimal($this->totalPrice);
        $this->totalVat = formatTakaToDecimal($this->totalVat);
        $this->totalItemDiscount = formatTakaToDecimal($this->totalItemDiscount);
        $this->totalBill = formatTakaToDecimal($this->totalBill);
        $this->due = formatTakaToDecimal($this->due);
        return $this;
    }

}
