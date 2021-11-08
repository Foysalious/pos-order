<?php namespace App\Services\DataMigration;

use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\DiscountRepositoryInterface;
use App\Interfaces\LogRepositoryInterface;
use App\Interfaces\OrderPaymentsRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkusRepositoryInterface;
use App\Interfaces\PartnerRepositoryInterface;
use App\Services\BaseService;
use App\Services\ClientServer\SmanagerUser\SmanagerUserServerClient;
use Carbon\Carbon;

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
    private $logs;
    /**
     * @var LogRepositoryInterface
     */
    private $logRepositoryInterface;
    private $customers;


    public function __construct(PartnerRepositoryInterface $partnerRepositoryInterface,
                                DiscountRepositoryInterface $discountRepositoryInterface,
                                OrderRepositoryInterface $orderRepositoryInterface,
                                OrderSkusRepositoryInterface $orderSkusRepositoryInterface,
                                OrderPaymentsRepositoryInterface $orderPaymentsRepositoryInterface,
                                LogRepositoryInterface $logRepositoryInterface,
                                private CustomerRepositoryInterface $customerRepository,
                                private SmanagerUserServerClient $smanagerUserServerClient)
    {
        $this->discountRepositoryInterface = $discountRepositoryInterface;
        $this->partnerRepositoryInterface = $partnerRepositoryInterface;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->orderSkusRepositoryInterface = $orderSkusRepositoryInterface;
        $this->orderPaymentsRepositoryInterface = $orderPaymentsRepositoryInterface;
        $this->logRepositoryInterface = $logRepositoryInterface;
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

    public function setOrderLogs($logs)
    {
        $this->logs = $logs;
        return $this;
    }

    public function setCustomers($customers)
    {
        $this->customers = $customers;
        return $this;
    }

    public function migrate()
    {
        if ($this->partnerInfo) {
            $this->migratePartnerInfoData();
        }
        if ($this->customers) {
            $this->migrateCustomersData();
        }
        if ($this->orders) {
            $this->migrateOrdersData();
        }
        if ($this->orderSkus) {
            $this->migrateOrderSkusData();
        }
        if ($this->orderPayments) {
            $this->migrateOrderPaymentsData();
        }
        if ($this->discounts) {
            $this->migrateOrderDiscountsData();
        }
        if ($this->logs) {
            $this->migrateOrderLogsData();
        }
    }

    private function migratePartnerInfoData()
    {
        $this->partnerRepositoryInterface->builder()->upsert($this->partnerInfo, ['id']);
    }

    private function migrateCustomersData()
    {
        $this->customerRepository->builder()->upsert($this->customers, ['id', 'partner_id']);
    }

    private function migrateOrdersData()
    {
        foreach ($this->orders as $order) {
            if($order['customer_id']) {
                $customer = $this->customerRepository->builder()->withTrashed()->where('id', $order['customer_id'])->where('partner_id', $order['partner_id'])->first();
                if(!$customer) {
                    $this->customerRepository->insert([
                        'id' => $order['customer_id'],
                        'partner_id' => $order['partner_id'],
                        'name' => $order['delivery_name'],
                        'mobile' => $order['delivery_mobile'],
                        'deleted_at' => convertTimezone(Carbon::now())?->format('Y-m-d H:i:s'),
                    ]);
                    $this->smanagerUserServerClient->post('/api/v1/partners/'. $order['partner_id'] .'/users/store-or-get', [
                        'previous_id' => $order['customer_id'],
                        'partner_id' => $order['partner_id'],
                        'name' => $order['delivery_name'],
                        'mobile' => $order['delivery_mobile'],
                        'deleted_at' => convertTimezone(Carbon::now())?->format('Y-m-d H:i:s'),
                    ]);
                }
            }
            $this->orderRepositoryInterface->insert($order);
        }
    }

    private function migrateOrderSkusData()
    {
        $this->orderSkusRepositoryInterface->insert($this->orderSkus);
    }

    private function migrateOrderPaymentsData()
    {
        $this->orderPaymentsRepositoryInterface->insert($this->orderPayments);
    }

    private function migrateOrderDiscountsData()
    {
        $this->discountRepositoryInterface->insert($this->discounts);
    }

    private function migrateOrderLogsData()
    {
        $this->logRepositoryInterface->insert($this->logs);
    }

}
