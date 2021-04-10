<?php namespace App\Services\DataMigration;

use App\Interfaces\DiscountRepositoryInterface;
use App\Interfaces\OrderPaymentsRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkusRepositoryInterface;
use App\Interfaces\PartnerRepositoryInterface;
use App\Services\BaseService;

class DataMigrationService extends BaseService
{
    private $orders;
    private $partnerInfo;
    private $discounts;
    private DiscountRepositoryInterface $discountRepositoryInterface;
    private PartnerRepositoryInterface $partnerRepositoryInterface;
    /** @var OrderRepositoryInterface */
    private OrderRepositoryInterface $orderRepositoryInterface;
    private $orderSkus;
    /** @var OrderSkusRepositoryInterface */
    private OrderSkusRepositoryInterface $orderSkusRepositoryInterface;
    /** @var OrderPaymentsRepositoryInterface */
    private OrderPaymentsRepositoryInterface $orderPaymentsRepositoryInterface;
    private $orderPayments;

    public function __construct(PartnerRepositoryInterface $partnerRepositoryInterface,
                                DiscountRepositoryInterface $discountRepositoryInterface,
                                OrderRepositoryInterface $orderRepositoryInterface,
                                OrderSkusRepositoryInterface $orderSkusRepositoryInterface,
                                OrderPaymentsRepositoryInterface $orderPaymentsRepositoryInterface)
    {
        $this->discountRepositoryInterface = $discountRepositoryInterface;
        $this->partnerRepositoryInterface = $partnerRepositoryInterface;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->orderSkusRepositoryInterface = $orderSkusRepositoryInterface;
        $this->orderPaymentsRepositoryInterface = $orderPaymentsRepositoryInterface;
    }

    public function setPartnerInfo($partnerInfo)
    {
        $this->partnerInfo = $partnerInfo;
        return $this;
    }

    public function setOrders($orders)
    {
        $this->orders = $orders;
        return $this;
    }

    public function setOrderSkus($orderSkus)
    {
        $this->orderSkus = $orderSkus;
        return $this;
    }

    public function setOrderPayments($orderPayments)
    {
        $this->orderPayments = $orderPayments;
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
        if ($this->orders) $this->migrateOrdersData();
        if ($this->orderSkus) $this->migrateOrderSkusData();
        if ($this->orderPayments) $this->migrateOrderPaymentsData();
        if ($this->discounts) $this->migrateOrderDiscountsData();
        return $this->success('Successful', ['partner' => $this->partnerInfo->id]);
    }

    private function migratePartnerInfoData()
    {
        $this->partnerRepositoryInterface->insertOrIgnore($this->partnerInfo);
    }

    private function migrateOrdersData()
    {
        $this->orderRepositoryInterface->insertOrIgnore($this->orders);
    }

    private function migrateOrderSkusData()
    {
        $this->orderSkusRepositoryInterface->insertOrIgnore($this->orderSkus);
    }

    private function migrateOrderPaymentsData()
    {
        $this->orderPaymentsRepositoryInterface->insertOrIgnore($this->orderPayments);
    }

    private function migrateOrderDiscountsData()
    {
        $this->discountRepositoryInterface->insertOrIgnore($this->discounts);
    }

}
