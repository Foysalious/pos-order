<?php

namespace App\Http\Resources;

use App\Models\OrderSku;
use App\Services\BaseService;
use App\Services\Inventory\InventoryServerClient;


class ProductIdAndName extends BaseService
{
    private InventoryServerClient $client;

    public function __construct(InventoryServerClient $client)
    {
        $this->client = $client;
    }

    public function getProductRatingReview(OrderSku $orderSku, $channel_id, $partner_id)
    {
        $orderSku = [$orderSku->id];
        $url = 'api/v1/partners/' . $partner_id . '/skus?skus=' . json_encode($orderSku) . '&channel_id=' . $channel_id;
        $response = $this->client->setBaseUrl()->get($url);
        $product_name = $response['skus'][0]['product_name'];
        $product_id = $response['skus'][0]['product_id'];
        $combination= $response['skus'][0]['combination'];
        return [$product_name, $product_id,$combination];
    }
}
