<?php


namespace App\Services\PaymentLink;


use App\Interfaces\PaymentLinkRepositoryInterface;

class Updater
{

    private int $linkId;
    private string $status;
    private $paymentLinkRepo;

    /**
     * Creator constructor.
     *
     * @param PaymentLinkRepositoryInterface $paymentLinkRepo
     */
    public function __construct(PaymentLinkRepositoryInterface $paymentLinkRepo)
    {
        $this->paymentLinkRepo = $paymentLinkRepo;
    }

    public function setPaymentLinkId($link_id)
    {
        $this->linkId = $link_id;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
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
}
