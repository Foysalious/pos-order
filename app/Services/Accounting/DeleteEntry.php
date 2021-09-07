<?php namespace App\Services\Accounting;


use App\Repositories\Accounting\Constants\EntryTypes;

class DeleteEntry extends BaseEntry
{
    public function delete()
    {
        $this->accountingRepository->deleteEntryBySource($this->order->partner_id,EntryTypes::POS, $this->order->id);
    }
}
