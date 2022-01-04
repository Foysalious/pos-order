<?php namespace App\Listeners;


use App\Events\OrderPlaceTransactionCompleted;
use App\Jobs\TrendingProductsCacheJob;
use App\Jobs\WebStoreSettingsSyncJob;
use App\Services\Accounting\UpdateEntry;
use App\Services\Webstore\SettingsSync\WebStoreSettingsSyncTypes;

class WebstoreSettingsSyncOnOrderCreate
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
     * @param OrderPlaceTransactionCompleted $event
     * @return void
     */
    public function handle(OrderPlaceTransactionCompleted $event)
    {
        TrendingProductsCacheJob::withChain([new WebStoreSettingsSyncJob($event->getOrder()->partner_id, WebStoreSettingsSyncTypes::Order, $event->getOrder()->id)])->dispatch($event->getOrder()->partner_id);
    }
}
