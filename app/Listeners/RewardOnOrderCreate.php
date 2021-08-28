<?php namespace App\Listeners;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use App\Events\OrderCreated;
use App\Jobs\Order\RewardOnOrderCreate as RewardOnOrderCreateJob;

class RewardOnOrderCreate
{
    use DispatchesJobs,SerializesModels;


    public function handle(OrderCreated $event)
    {
        $this->dispatch((new RewardOnOrderCreateJob($event->getOrder())));
    }

}