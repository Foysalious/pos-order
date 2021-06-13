<?php namespace App\Services\PaymentLink;


use App\Interfaces\PaymentLinkRepositoryInterface;
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

    /**
     * Creator constructor.
     *
     * @param PaymentLinkRepositoryInterface $payment_link_repository
     */
    public function __construct(PaymentLinkRepositoryInterface $payment_link_repository)
    {
        $this->paymentLinkRepo                = $payment_link_repository;
        $this->isDefault                      = 0;
        $this->amount                         = null;
        $this->partnerProfit                  = 0;
    }

    public function setAmount($amount)
    {
        $this->amount = round($amount, 2);
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

    /**
     * @param mixed $payerId
     * @return Creator
     */
    public function setPayerId($payerId)
    {
        $this->payerId = $payerId;
        return $this;
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

    private function makeData()
    {
        $this->data = [
            'amount'                => $this->amount,
            'reason'                => $this->reason,
            'isDefault'             => $this->isDefault,
            'userId'                => $this->userId,
            'userName'              => $this->userName,
            'userType'              => $this->userType,
            'targetId'              => (int)$this->targetId,
            'targetType'            => $this->targetType,
            'payerId'               => $this->payerId,
            'payerType'             => $this->payerType,
            'emiMonth'              => $this->emiMonth,
            'interest'              => $this->interest,
            'bankTransactionCharge' => $this->bankTransactionCharge,
            'paidBy'                => $this->paidBy,
            'partnerProfit'         => $this->partnerProfit
        ];
        if ($this->isDefault) unset($this->data['reason']);
        if (!$this->targetId) unset($this->data['targetId'], $this->data['targetType']);
    }
}
