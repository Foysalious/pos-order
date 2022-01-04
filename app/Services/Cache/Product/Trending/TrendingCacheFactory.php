<?php namespace App\Services\Cache\Product\Trending;

use App\Services\Cache\CacheFactory;
use App\Services\Cache\CacheObject;
use App\Services\Cache\CacheRequest;
use App\Services\Cache\DataStoreObject;

class TrendingCacheFactory implements CacheFactory
{

    public function getCacheObject(CacheRequest $cacheRequest): CacheObject
    {
        $review_cache = new TrendingCache();
        $review_cache->setCacheRequest($cacheRequest);
        return $review_cache;
    }

    public function getDataStoreObject(CacheRequest $cacheRequest): DataStoreObject
    {
        $review_data_store = new TrendingDataStore();
        $review_data_store->setCacheRequest($cacheRequest);
        return $review_data_store;
    }
}
