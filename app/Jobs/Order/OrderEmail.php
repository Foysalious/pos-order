<?php

namespace App\Jobs\Order;

use App\Jobs\Job;
use App\Models\Order;
use App\Models\Partner;
use App\Services\Order\EmailHandler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class OrderEmail extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    private Order $order;
    private Partner $partner;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     * @param EmailHandler $handler
     */

    public function handle(EmailHandler $handler)
    {
        if ($this->attempts() <= 2) {
            $handler->setOrder($this->order)->handle();
        }
    }
}
