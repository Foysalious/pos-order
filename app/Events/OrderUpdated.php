<?php namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderUpdated
{
    use Dispatchable, SerializesModels;

    protected Order $order;
    protected array $orderProductChangeData;

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
    public function getOrderProductChangedData(): array
    {
        return $this->orderProductChangeData;
    }
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Order $order, array $orderProductChangeData)
    {
        $this->order = $order;
        $this->orderProductChangeData = $orderProductChangeData;
    }

}
