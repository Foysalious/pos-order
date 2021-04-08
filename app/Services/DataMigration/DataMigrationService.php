<?php namespace App\Services\DataMigration;

use App\Interfaces\DiscountRepositoryInterface;
use App\Interfaces\PartnerRepositoryInterface;

class DataMigrationService
{
    private $orders;
    private $partnerInfo;
    private $discounts;
    private DiscountRepositoryInterface $discountRepositoryInterface;
    private PartnerRepositoryInterface $partnerRepositoryInterface;

    public function setPartnerInfo($partnerInfo , PartnerRepositoryInterface $partnerRepositoryInterface, DiscountRepositoryInterface $discountRepositoryInterface)
    {
        $this->partnerInfo = $partnerInfo;
        $this->discountRepositoryInterface = $discountRepositoryInterface;
        $this->partnerRepositoryInterface =$partnerRepositoryInterface;
        return $this;
    }


    public function setOrders($orders)
    {
        $this->orders = $orders;
        return $this;
    }

    public function setDiscounts($discounts)
    {
        $this->discounts = $discounts;
        return $this;
    }

    public function migrate()
    {
        if ($this->partnerInfo) $this->migratePartnerInfoData();
        if ($this->discounts) $this->migrateOrderDiscountsData();
    }

    private function migratePartnerInfoData()
    {
        $this->partnerRepositoryInterface->insertOrIgnore($this->partnerInfo);
    }

    private function migrateOrderDiscountsData()
    {
        $this->discountRepositoryInterface->insertOrIgnore($this->discounts);
    }


}
