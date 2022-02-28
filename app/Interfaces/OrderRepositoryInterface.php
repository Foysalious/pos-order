<?php namespace App\Interfaces;


use App\Helper\TimeFrame;
use App\Services\Order\Constants\SalesChannelIds;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    public function getCustomerOrderList(string $customer_id, int $offset, int $limit, string $orderBy, string $order);
    public function getCustomerOrderCount(string $customer_id);
    public function getVoucherInformation($voucher_id);
    public function getOrderDetailsByPartner(int $partnerId, int $orderId);
    public function getOrderStatusStatByPartner(int $partnerId, $salesChannelIds = [SalesChannelIds::POS, SalesChannelIds::WEBSTORE]);
    public function getOrdersBetweenDatesByPartner(int $partnerId, TimeFrame $time_frame, $salesChannelIds = [SalesChannelIds::POS, SalesChannelIds::WEBSTORE]);
    public function getAllOrdersOfPartnersCustomer(int $partner_id, $customer_id, $sort_order, $limit, $skip);
    public function getPartnerWiseOrderIdsFromOrderIds(array $orderIds, int $offset, int $limit);
}
