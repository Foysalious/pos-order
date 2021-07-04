<?php namespace App\Repositories\Accounting;

use App\Models\Partner;
use App\Services\Accounting\AccountingEntryClient;
use App\Services\Accounting\Exceptions\AccountingEntryServerError;
use App\Services\FileManagers\CdnFileManager;
use App\Services\FileManagers\FileManager;
use App\Traits\ModificationFields;

class BaseRepository
{
    use ModificationFields, CdnFileManager, FileManager;

    /** @var AccountingEntryClient $client */
    protected $client;

    /**
     * BaseRepository constructor.
     * @param AccountingEntryClient $client
     */
    public function __construct(AccountingEntryClient $client)
    {
        $this->client = $client;
    }

}
