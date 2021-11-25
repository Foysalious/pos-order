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
        $data['customer_updated'] = 1;
        $this->accountingRepository->updateEntryBySource($data, $this->order->id, $this->order->partner_id);
    }

}
