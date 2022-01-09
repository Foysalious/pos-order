<?php namespace App\Repositories\Accounting;

use App\Models\EventNotification;
use App\Services\Accounting\AccountingEntryClient;
use App\Services\FileManagers\CdnFileManager;
use App\Services\FileManagers\FileManager;
use App\Traits\ModificationFields;

class BaseRepository
{
    use ModificationFields, CdnFileManager, FileManager;

    /** @var AccountingEntryClient $client */
    protected AccountingEntryClient $client;
    protected EventNotification $eventNotification;

    /**
     * BaseRepository constructor.
     * @param AccountingEntryClient $client
     */
    public function __construct(AccountingEntryClient $client)
    {
        $this->client = $client;
    }

    protected function setEventNotification(EventNotification $eventNotification): static
    {
        $this->eventNotification = $eventNotification;
        return $this;
    }

}
