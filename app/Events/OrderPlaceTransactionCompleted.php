<?php namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPlaceTransactionCompleted
{
    use Dispatchable, SerializesModels;

    protected Order $order;

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

}
