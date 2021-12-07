<?php namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderDueCleared
{
    use Dispatchable, SerializesModels;

    protected Order $order;
    protected ?float $paidAmount;

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return float|null
     */
    public function getPaidAmount(): float|null
    {
        return $this->paidAmount;
    }



    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $event_data)
    {
        $this->order = $event_data['order'];
        $this->paidAmount = $event_data['paid_amount'];
    }

}
