<?php namespace App\Jobs\Order;


use App\Models\Order;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Order\Notification\OrderPlacePushNotificationHandler;

class OrderPlacePushNotification
{
    use InteractsWithQueue, SerializesModels;

    private Order $order;
    protected int $tries = 1;

    /**
     * Create a new job instance.
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(OrderPlacePushNotificationHandler $handler)
    {
        if ($this->attempts() > 2) return;
        $handler->setOrder($this->order)->handle();
    }
}
