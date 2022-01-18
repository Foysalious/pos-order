<?php namespace App\Services\Cache\Product\Trending;


use App\Interfaces\OrderSkuRepositoryInterface;
use App\Models\Order;
use App\Services\Cache\CacheRequest;
use App\Services\Cache\DataStoreObject;
use App\Services\Order\OrderService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TrendingDataStore implements DataStoreObject
{
    /** @var TrendingCacheRequest */
    private $trendingCacheRequest;

    public function setCacheRequest(CacheRequest $request)
    {
        $this->trendingCacheRequest = $request;
        return $this;
    }

    public function generate()
    {
        $partnerOrder = Order::where('partner_id', $this->trendingCacheRequest->getPartnerId())->where('sales_channel_id', 2)->get();
        if (count($partnerOrder) < 1) throw new NotFoundHttpException('no product Found');
        /** @var OrderSkuRepositoryInterface $OrderSkuRepositoryInterface */
        $OrderSkuRepositoryInterface = app(OrderSkuRepositoryInterface::class);
        $trending = $OrderSkuRepositoryInterface->getTrendingProducts($this->trendingCacheRequest->getPartnerId());
        /** @var OrderService $orderService */
        $orderService = app(OrderService::class);
        $products = $orderService->getSkuDetailsForWebstore($this->trendingCacheRequest->getPartnerId(), $trending);
        return $products->getData()->data;
    }

}
