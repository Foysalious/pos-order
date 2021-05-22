<?php namespace App\Services\OrderSms;

use App\Jobs\Job;
use App\Models\Order;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\OrderSms\WebstoreOrderSmsHandler;

class WebstoreOrderSms extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var Order
     */
    private $order;
    protected $tries = 1;
    protected $status;

    /**
     * Create a new job instance.
     * @param Order $order
     * @param $status
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     * @param WebstoreOrderSmsHandler $handler
     * @throws Exception
     */
    public function handle(WebstoreOrderSmsHandler $handler)
    {
dd(1);
        if ($this->attempts() > 2) return;
        $handler->setOrder($this->order)->handle();
    }

}
