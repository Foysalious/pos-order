<?php namespace App\Services\Accounting;


use App\Repositories\Accounting\Constants\EntryTypes;

class DeleteEntry extends BaseEntry
{
    public function delete()
    {
        $this->accountingRepository->setOrder($this->order)->deleteEntryBySource(EntryTypes::POS);
    }
}
