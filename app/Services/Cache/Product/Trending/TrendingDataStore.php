<?php namespace App\Services\Cache\Product\Trending;


use App\Interfaces\OrderSkuRepositoryInterface;
use App\Models\Order;
use App\Services\Cache\CacheRequest;
use App\Services\Cache\DataStoreObject;
use App\Services\Order\OrderService;
use App\Traits\ResponseAPI;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TrendingDataStore implements DataStoreObject
{
    use ResponseAPI;

    /** @var TrendingCacheRequest */
    private $trendingCacheRequest;

    public function setCacheRequest(CacheRequest $request)
    {
        $this->trendingCacheRequest = $request;
        return $this;
    }

    public function generate()
    {
        /** @var OrderSkuRepositoryInterface $OrderSkuRepositoryInterface */
        $OrderSkuRepositoryInterface = app(OrderSkuRepositoryInterface::class);
        $trending = $OrderSkuRepositoryInterface->getTrendingProducts($this->trendingCacheRequest->getPartnerId());
        if (count($trending) == 0) return null;
        /** @var OrderService $orderService */
        $orderService = app(OrderService::class);
        $products = $orderService->getSkuDetailsForWebstore($this->trendingCacheRequest->getPartnerId(), $trending);
        return $products->getData()->data;
    }

}
