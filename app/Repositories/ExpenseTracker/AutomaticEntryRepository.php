<?php namespace App\Repositories\ExpenseTracker;

use App\Helper\Miscellaneous\RequestIdentification;
use App\Repositories\Accounting\Constants\EntryTypes;
use App\Services\ExpenseTracker\Constants\AutomaticExpense;
use App\Services\ExpenseTracker\Exceptions\InvalidHeadException;
use Carbon\Carbon;
use Exception;
use Sheba\ExpenseTracker\AutomaticIncomes;
use Throwable;

class AutomaticEntryRepository extends BaseRepository
{
    private $head;
    private $amount;
    private $result;
    private $for;
    private $amountCleared;
    private $sourceType;
    private $sourceId;
    private $createdAt;
    private $emiMonth;
    private $paymentId;
    private $interest;
    private $bankTransactionCharge;
    private $isWebstoreOrder = 0;
    private $isPaymentLink = 0;
    private $isDueTrackerPaymentLink=0;
    private $paymentMethod;

    /**
     * @param mixed $paymentId
     * @return AutomaticEntryRepository
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;
        return $this;
    }

    /**
     * @param mixed $source_type
     * @return AutomaticEntryRepository
     */
    public function setSourceType($source_type)
    {
        $this->sourceType = $source_type;
        return $this;
    }

    /**
     * @param mixed $source_id
     * @return AutomaticEntryRepository
     */
    public function setSourceId($source_id)
    {
        $this->sourceId = $source_id;
        return $this;
    }

    /**
     * @param mixed $for
     * @return AutomaticEntryRepository
     */
    public function setFor($for)
    {
        $this->for = $for;
        return $this;
    }

    /**
     * @param mixed $result
     * @return AutomaticEntryRepository
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @param mixed $amount
     * @return AutomaticEntryRepository
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param $head
     * @return AutomaticEntryRepository
     */
    public function setHead($head)
    {
        try {
            $this->validateHead($head);
            $this->head = $head;
            return $this;
        } catch (Throwable $e) {
            $this->notifyBug($e);
            return $this;
        }
    }

    /**
     * @param $isWebstoreOrder
     * @return AutomaticEntryRepository
     */
    public function setIsWebstoreOrder($isWebstoreOrder)
    {
        $this->isWebstoreOrder = $isWebstoreOrder;
        return $this;
    }

    /**
     * @param $head
     * @throws InvalidHeadException
     */
    private function validateHead($head)
    {
        if (!in_array($head, AutomaticIncomes::heads()) && !in_array($head, AutomaticExpense::heads()))
            throw new InvalidHeadException();
        if (in_array($head, AutomaticExpense::heads()))
            $this->for = EntryTypes::EXPENSE; else $this->for = EntryTypes::INCOME;
    }

    private function notifyBug(Throwable $e)
    {
        app('sentry')->captureException($e);
    }

    /**
     * @param Carbon $created_at
     * @return $this
     */
    public function setCreatedAt(Carbon $created_at)
    {
        try {
            $this->createdAt = $created_at->format('Y-m-d H:i:s');
            return $this;
        } catch (Throwable $e) {
            $this->createdAt = Carbon::now()->format('Y-m-d H:i:s');
            $this->notifyBug($e);
            return $this;
        }
    }

    /**
     * @param mixed $amount_cleared
     * @return AutomaticEntryRepository
     */
    public function setAmountCleared($amount_cleared)
    {
        $this->amountCleared = $amount_cleared;
        return $this;
    }


    /**
     * @param mixed $emiMonth
     * @return AutomaticEntryRepository
     */
    public function setEmiMonth($emiMonth)
    {
        $this->emiMonth = $emiMonth;
        return $this;
    }

    /**
     * @param mixed $interest
     * @return AutomaticEntryRepository
     */
    public function setInterest($interest)
    {
        $this->interest = $interest;
        return $this;
    }

    /**
     * @param mixed $bankTransactionCharge
     * @return AutomaticEntryRepository
     */
    public function setBankTransactionCharge($bankTransactionCharge)
    {
        $this->bankTransactionCharge = $bankTransactionCharge;
        return $this;
    }



    /**
     * @return mixed
     * @throws Exception
     */
    private function getData()
    {
        $created_from               = $this->withBothModificationFields((new RequestIdentification())->get());
        $created_from['created_at'] = $created_from['created_at']->format('Y-m-d H:i:s');
        $created_from['updated_at'] = $created_from['updated_at']->format('Y-m-d H:i:s');
        $data                       = [
            'created_at'              => $this->createdAt ?: Carbon::now()->format('Y-m-d H:i:s'),
            'created_from'            => json_encode($created_from),
            'amount'                  => $this->amount,
            'amount_cleared'          => $this->amountCleared,
            'head_name'               => $this->head,
            'note'                    => $this->isPaymentLink ? 'Automatically Placed from Sheba payment link':'Automatically Placed from Sheba',
            'source_type'             => $this->sourceType,
            'source_id'               => $this->sourceId,
            'type'                    => $this->for,
            'payment_method'          => $this->paymentMethod,
            'payment_id'              => $this->paymentId,
            'emi_month'               => $this->emiMonth,
            'interest'                => $this->interest,
            'bank_transaction_charge' => $this->bankTransactionCharge,
            'is_webstore_order'       => $this->isWebstoreOrder,
            'is_payment_link'         => $this->isPaymentLink,
            'is_due_tracker_payment_link'=>$this->isDueTrackerPaymentLink
        ];
        return $data;
    }


    public function updateFromSrc()
    {
        try {
            $data = $this->getData();
            if (empty($data['source_type']) || empty($data['source_id']))
                throw new Exception('Source Type or Source id is not present');
            $this->result = $this->client->post('accounts/' . $this->accountId . '/entries/from-type', $data)['data'];
            return $this->result;
        } catch (Throwable $e) {
            $this->notifyBug($e);
            return false;
        }
    }
}
