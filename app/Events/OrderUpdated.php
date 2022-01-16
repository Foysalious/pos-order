<?php namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderUpdated
{
    use Dispatchable, SerializesModels;

    protected Order $order;
    protected array $orderProductChangeData;
    protected array $payment_info;
    protected array $stockUpdateData;
    private array $previousOrder;

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    public function getPreviousOrder()
    {
        return $this->previousOrder;
    }

    /**
     * @return array
     */
    public function getOrderProductChangedData(): array
    {
        return $this->orderProductChangeData;
    }

    /**
     * @return array
     */
    public function getPaymentInfo(): array
    {
        return $this->payment_info;
    }

    /**
     * @return mixed
     */
    public function getStockUpdateData(): mixed
    {
        return $this->stockUpdateData;
    }
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $event_data)
    {
        $this->order = $event_data['order'];
        $this->previousOrder = $event_data['previous_order'];
        $this->orderProductChangeData = $event_data['order_product_change_data'];
        $this->payment_info = $event_data['payment_info'];
        $this->stockUpdateData = $event_data['stock_update_data'];
    }

}
