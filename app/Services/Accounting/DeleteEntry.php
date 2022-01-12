<?php namespace App\Services\Accounting;


use App\Repositories\Accounting\Constants\EntryTypes;
use App\Services\ClientServer\Exceptions\BaseClientServerError;

class DeleteEntry extends BaseEntry
{
    /**
     * @throws BaseClientServerError
     */
    public function delete()
    {
        $this->getNotifier()->deleteEntryBySource(EntryTypes::POS);
    }
}
