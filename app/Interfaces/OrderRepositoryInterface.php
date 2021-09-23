<?php namespace App\Interfaces;


use App\Helper\TimeFrame;
use App\Services\Order\Constants\SalesChannelIds;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    public function getCustomerOrderList(string $customer_id, int $offset, int $limit, string $orderBy, string $order);
    public function getCustomerOrderCount(string $customer_id);
    public function getVoucherInformation($voucher_id);
    public function getOrderDetailsByPartner(int $partnerId, int $orderId);
    public function getOrderStatusStatByPartner(int $partnerId);
    public function getOrdersBetweenDatesByPartner(int $partnerId, TimeFrame $time_frame, $salesChannelIds = [SalesChannelIds::POS, SalesChannelIds::WEBSTORE]);
}
