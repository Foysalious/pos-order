<?php namespace App\Listeners;


use App\Events\OrderUpdated;
use App\Jobs\Usage\UsageJob;
use App\Services\Usage\Types;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class UsageOnOrderUpdate
{
    use DispatchesJobs,SerializesModels;

    /**
     * Handle the event.
     *
     * @param OrderUpdated $event
     * @return void
     */
    public function handle(OrderUpdated $event)
    {
        $this->dispatch((new UsageJob((int) $event->getOrder()->partner_id, Types::POS_ORDER_UPDATE)));
    }
}
