<?php namespace App\Services\Cache;

interface CacheFactory
{
    public function getCacheObject(CacheRequest $cacheRequest): CacheObject;

    public function getDataStoreObject(CacheRequest $cacheRequest): DataStoreObject;
}
