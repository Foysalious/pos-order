<?php namespace App\Listeners;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use App\Events\RewardOnOrderCreate as RewardOnOrderCreateEvent;
use App\Jobs\Order\RewardOnOrderCreate as RewardOnOrderCreateJob;

class RewardOnOrderCreate
{
    use DispatchesJobs,SerializesModels;


    public function handle(RewardOnOrderCreateEvent $event)
    {
        $this->dispatch((new RewardOnOrderCreateJob($event->model)));
    }

}
