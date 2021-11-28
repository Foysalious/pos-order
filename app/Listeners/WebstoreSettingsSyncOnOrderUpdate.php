<?php namespace App\Listeners;


use App\Events\OrderUpdated;
use App\Jobs\WebStoreSettingsSyncJob;
use App\Services\Accounting\UpdateEntry;
use App\Services\Webstore\SettingsSync\WebStoreSettingsSyncTypes;

class WebstoreSettingsSyncOnOrderUpdate
{
    protected UpdateEntry $updateEntry;

    /**
     * AccountingEntryOnOrderUpdating constructor.
     * @param UpdateEntry $updateEntry
     */
    public function __construct(UpdateEntry $updateEntry)
    {
        $this->updateEntry = $updateEntry;
    }


    /**
     * Handle the event.
     *
     * @param OrderUpdated $event
     * @return void
     */
    public function handle(OrderUpdated $event)
    {
        dispatch(new WebStoreSettingsSyncJob($event->getOrder()->partner_id, WebStoreSettingsSyncTypes::Order, $event->getOrder()->id));
    }
}
