<?php

namespace App\Services\Accounting;

class CustomerUpdateEntry extends CreateEntry
{

    /**
     * @throws Exceptions\AccountingEntryServerError|\Exception
     */
    public function customerUpdateEntry()
    {
        $this->getNotifier()->updateEntryBySource($this->makeData(), $this->order->id, $this->order->partner_id);
    }

}
