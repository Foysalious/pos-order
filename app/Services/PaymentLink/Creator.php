<?php namespace App\Services\PaymentLink;


use App\Interfaces\PaymentLinkRepositoryInterface;
use App\Models\Customer;
use App\Services\PaymentLink\Exceptions\Calculations;
use GuzzleHttp\Exception\GuzzleException;
use stdClass;

class Creator
{
    private $paymentLinkRepo;
    private $amount;
    private $reason;
    private $userId;
    private $userName;
    private $userType;
    private $isDefault;
    private $targetId;
    private $targetType;
    private $data;
    private $paymentLinkCreated;
    private $emiMonth;
    private $payerId;
    private $payerType;
    private $interest;
    private $bankTransactionCharge;
    private $paidBy;
    private $partnerProfit;
    private mixed $realAmount;
    private $linkId;
    private $status;

    /**
     * Creator constructor.
     *
     * @param PaymentLinkRepositoryInterface $payment_link_repository
     */
    public function __construct(PaymentLinkRepositoryInterface $payment_link_repository)
    {
        $this->paymentLinkRepo = $payment_link_repository;
        $this->isDefault = 0;
        $this->amount = null;
        $this->partnerProfit = 0;
    }

    public function setAmount($amount)
    {
        $this->amount = round($amount, 2);
        return $this;
    }

    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    public function setReason($reason)
    {
        $this->reason = $reason;
        return $this;
    }

    public function setUserId($user_id)
    {
        $this->userId = $user_id;
        return $this;
    }

    public function getPaidBy()
    {
        return $this->paidBy;
    }

    public function getInterest()
    {
        return $this->interest;
    }

    public function getBankTransactionCharge()
    {
        return $this->bankTransactionCharge;
    }

    public function setUserName($user_name)
    {
        $this->userName = (empty($user_name)) ? "Unknown Name" : $user_name;
        return $this;
    }

    public function setUserType($user_type)
    {
        $this->userType = $user_type;
        return $this;
    }

    public function setTargetId($target_id)
    {
        $this->targetId = $target_id;
        return $this;
    }

    public function setTargetType($target_type)
    {
        $this->targetType = $target_type;
        return $this;
    }

    public function setTransactionFeePercentage($transaction_charge)
    {
        $this->transaction_charge = $transaction_charge ?: config('payment_link.payment_link_commission');
        return $this;
    }

    public function setPaidBy($interest_paid_by)
    {
        $this->interest_paid_by = $interest_paid_by;
        return $this;
    }

    public function setEmiMonth($emi_month)
    {
        $this->emi_month = $emi_month;
        return $this;
    }

    /**
     * @param mixed $payerId
     * @return Creator
     */
    public function setPayerId($payerId)
    {
        $this->payerId = $payerId;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function setPaymentLinkId($link_id)
    {
        $this->linkId = $link_id;
        return $this;
    }

    public function editStatus()
    {
        if ($this->status == 'active') {
            $this->status = 1;
        } else {
            $this->status = 0;
        }
        return $this->paymentLinkRepo->statusUpdate($this->linkId, $this->status);
    }

    /**
     * @param mixed $payerType
     * @return Creator
     */
    public function setPayerType($payerType)
    {
        $this->payerType = $payerType;
        return $this;
    }

    public function setPartnerProfit($partnerProfit)
    {
        $this->partnerProfit = $partnerProfit;
        return $this;
    }

    public function setBankTransactionCharge($bankTransactionCharge)
    {
        $this->bankTransactionCharge = round($bankTransactionCharge, 2);
        return 2;
    }

    public function setInterest($interest)
    {
        $this->interest = round($interest, 2);
        return $this;
    }

    public static function validateEmiMonth($data, $type = 'manager')
    {
        if (isset($data['emi_month']) && $data['emi_month'] && (double)$data['amount'] < config('emi.' . $type . '.minimum_emi_amount')) return 'Amount must be greater then or equal BDT ' . config('emi.' . $type . '.minimum_emi_amount');
        return false;
    }

    /**
     * @return stdClass|null
     * @throws GuzzleException
     */
    public function create()
    {
        $this->makeData();
        $this->paymentLinkCreated = $this->paymentLinkRepo->create($this->data);
        return $this->paymentLinkCreated;
    }

    private function getPayerInfo()
    {
        $payerInfo = [];
        if ($this->paymentLinkCreated->payerId) {
            try {
                /** @var Customer $payer */
                $payer = Customer::find($this->paymentLinkCreated->payerId);
                $details = $payer ? $payer : null;
                if ($details) {
                    $payerInfo = [
                        'payer' => [
                            'id' => $payer->id,
                            'name' => $payer->id,
                            'mobile' => $payer->mobile
                        ]
                    ];
                }
            } catch (\Throwable $e) {
                throw $e;
            }
        }
        return $payerInfo;
    }

    public function getPaymentLinkData()
    {
        $payer = null;
        $payerInfo = $this->getPayerInfo();
        return array_merge([
            'link_id' => $this->paymentLinkCreated->linkId,
            'reason' => $this->paymentLinkCreated->reason,
            'type' => $this->paymentLinkCreated->type,
            'status' => $this->paymentLinkCreated->isActive == 1 ? 'active' : 'inactive',
            'amount' => $this->paymentLinkCreated->amount,
            'link' => $this->paymentLinkCreated->link,
            'emi_month' => $this->paymentLinkCreated->emiMonth,
            'interest' => $this->paymentLinkCreated->interest,
            'bank_transaction_charge' => $this->paymentLinkCreated->bankTransactionCharge,
            'paid_by' => $this->paymentLinkCreated->paidBy,
            'partner_profit' => $this->paymentLinkCreated->partnerProfit
        ], $payerInfo);
    }

    private function makeData()
    {
        $this->data = [
            'amount' => $this->amount,
            'reason' => $this->reason,
            'isDefault' => $this->isDefault,
            'userId' => $this->userId,
            'userName' => $this->userName,
            'userType' => 'partner',
            'targetId' => (int)$this->targetId,
            'targetType' => $this->targetType,
            'payerId' => $this->payerId,
            'payerType' => $this->payerType,
            'emiMonth' => $this->emiMonth,
            'interest' => $this->interest,
            'bankTransactionCharge' => $this->bankTransactionCharge,
            'paidBy' => $this->paidBy,
            'partnerProfit' => $this->partnerProfit,
            'realAmount' => $this->realAmount
        ];
        if ($this->isDefault) unset($this->data['reason']);
        if (!$this->targetId) unset($this->data['targetId'], $this->data['targetType']);
        return $this->data;
    }

    public function calculate()
    {
        $amount = $this->amount;
        if ($this->paidBy != 'partner') {
            if ($this->emiMonth) {
                $data = Calculations::getMonthData($amount, $this->emiMonth, false, $this->transaction_charge);
                $this->setInterest($data['total_interest'])->setBankTransactionCharge($data['bank_transaction_fee'] + config('payment_link.payment_link_tax'))->setAmount($data['total_amount'] + config('payment_link.payment_link_tax'))->setPartnerProfit($data['partner_profit']);
            } else {
                $this->setAmount($amount + round($amount * $this->transaction_charge / 100, 2) + config('payment_link.payment_link_tax'))
                    ->setPartnerProfit($this->amount - ($amount + round($amount * config('payment_link.payment_link_commission') / 100, 2) + config('payment_link.payment_link_tax')))
                    ->setRealAmount($amount);
            }
        } else {
            if ($this->emiMonth) {
                $data = Calculations::getMonthData($amount, $this->emiMonth, false);
                $this->setInterest($data['total_interest'])
                    ->setBankTransactionCharge($data['bank_transaction_fee'])
                    ->setAmount($amount);
            }
        }
        return $this;
    }

    public function getErrorMessage($status = false)
    {
        if ($status) {
            $type = $status === "active" ? "সক্রিয়" : "নিষ্ক্রিয়";
            $message = 'দুঃখিত! কিছু একটা সমস্যা হয়েছে, লিঙ্ক ' . $type . ' করা সম্ভব হয়নি। অনুগ্রহ করে আবার চেষ্টা করুন।';
            $title = 'লিংকটি ' . $type . ' করা সম্ভব হয়নি';
            return ["message" => $message, "title" => $title];
        }
        $message = 'দুঃখিত! কিছু একটা সমস্যা হয়েছে, লিঙ্ক তৈরি করা সম্ভব হয়নি। অনুগ্রহ করে আবার চেষ্টা করুন।';
        $title = 'লিঙ্ক তৈরি হয়নি';
        return ["message" => $message, "title" => $title];
    }

    /**
     * @param mixed $realAmount
     * @return Creator
     */
    public function setRealAmount($realAmount)
    {
        $this->realAmount = $realAmount;
        return $this;
    }
}
