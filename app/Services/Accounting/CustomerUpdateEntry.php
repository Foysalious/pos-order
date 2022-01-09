<?php

namespace App\Services\Accounting;

class CustomerUpdateEntry extends CreateEntry
{

    /**
     * @throws Exceptions\AccountingEntryServerError
     */
    public function customerUpdateEntry()
    {
        $data = $this->makeData();
        $this->accountingRepository
            ->setOrder($this->order)
            ->updateEntryBySource($data, $this->order->id, $this->order->partner_id);
    }

}
