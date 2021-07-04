<?php namespace App\Services\Order\Refund;

use App\Models\Order;

class OrderUpdateFactory
{

    public static function getProductAddingUpdater(Order $order, $data)
    {
        return (app(AddProductInOrder::class))->setOrder($order)->setData($data);
    }

    public static function getProductDeletionUpdater(Order $order, $data)
    {
        return (app(DeleteProductFromOrder::class))->setOrder($order)->setData($data);
    }

    public static function getOrderProductUpdater(Order $order, $data)
    {
        return (app(UpdateProductInOrder::class))->setOrder($order)->setData($data);
    }
}
