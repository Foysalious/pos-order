<?php namespace App\Services\Cache\Product\Trending;


use App\Services\Cache\CacheName;
use App\Services\Cache\CacheObject;
use App\Services\Cache\CacheRequest;

class TrendingCache implements CacheObject
{
    private TrendingCacheRequest $trendingCacheRequest;

    public function getCacheName(): string
    {
        return sprintf("%s::%s::%d", $this->getRedisNamespace(), 'partner', $this->trendingCacheRequest->getPartnerId());
    }

    public function getRedisNamespace(): string
    {
        return CacheName::TRENDING_PRODUCTS;
    }

    public function getExpirationTimeInSeconds(): int
    {
        return 24 * 60 * 60;
    }

    public function setCacheRequest(CacheRequest $cache_request)
    {
        $this->trendingCacheRequest = $cache_request;
        return $this;
    }

    public function getAllKeysRegularExpression(): string
    {
        $partner_id = $this->trendingCacheRequest->getPartnerId();
        return $this->getRedisNamespace() . "::partner::" . ($partner_id ? $partner_id : "*");
    }
}
