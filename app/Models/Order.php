<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends BaseModel
{
    protected $guarded = ['id'];
    use HasFactory;

    public function calculate()
    {
        $this->_calculateThisItems();

    }
    public function orderSkus()
    {
        return $this->hasMany(OrderSku::class);
    }

    private function _calculateThisItems()
    {
        $this->_initializeTotalsToZero();
        foreach ($this->orderSkus as $order_sku) {
            /** @var OrderSku $order_sku */
            $order_sku = $order_sku->calculate();
        }
        return $this;
    }

    private function _initializeTotalsToZero()
    {
        $this->totalPrice = 0;
        $this->totalVat = 0;
        $this->totalItemDiscount = 0;
        $this->totalBill = 0;
    }


}
