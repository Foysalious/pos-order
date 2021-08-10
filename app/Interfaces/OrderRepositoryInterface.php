<?php namespace App\Interfaces;


interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    public function getOrderListWithPagination($offset, $limit, $partner_id, $orderSearch, $orderFilter);
    public function getCustomerOrderList(string $customer_id, int $offset, int $limit, string $orderBy, string $order);
    public function getCustomerOrderCount(string $customer_id);
    public function getVoucherInformation($voucher_id);
    public function getOrderDetailsByPartner(int $partnerId, int $orderId);
}
