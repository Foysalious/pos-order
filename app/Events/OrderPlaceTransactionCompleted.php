<?php namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPlaceTransactionCompleted
{
    use Dispatchable, SerializesModels;

    protected Order $order;
    protected array $stockUpdateData;

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return array
     */
    public function getStockUpdateData(): array
    {
        return $this->stockUpdateData;
    }
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Order $order, array $stock_update_data)
    {
        $this->order = $order;
        $this->stockUpdateData = $stock_update_data;
    }

}
