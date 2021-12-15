<?php

namespace App\Jobs\Order;

use App\Models\Order;
use App\Models\Partner;
use App\Services\APIServerClient\ApiServerClient;
use App\Services\Order\EmailHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class OrderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        app('sentry')->captureException($exception);
    }
}
