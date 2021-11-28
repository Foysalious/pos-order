<?php

namespace App\Http\Resources;

use App\Models\OrderSku;
use App\Services\BaseService;
use App\Services\Inventory\InventoryServerClient;


class ProductRatingReview extends BaseService
{
    private InventoryServerClient $client;

    public function __construct(InventoryServerClient $client)
    {
        $this->client = $client;
    }

    public function getProductRatingReview(OrderSku $orderSku, $channel_id, $partner_id)
    {
        $skus = [$orderSku->sku_id];
        $url = 'api/v1/partners/' . $partner_id . '/skus?skus=' . json_encode($skus) . '&channel_id=' . $channel_id;
        $response = $this->client->get($url);
        $product_name = $response['skus'] ? $response['skus'][0]['product_name'] : null;
        $product_id = $response['skus'] ? $response['skus'][0]['product_id'] : null;
        return [$product_name, $product_id];
    }
}
