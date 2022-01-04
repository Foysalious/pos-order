<?php namespace App\Services\Cache\Product\Trending;


use App\Services\Cache\CacheName;
use App\Services\Cache\CacheRequest;

class TrendingCacheRequest implements CacheRequest
{
    private int $partnerId;

    public function setPartnerId($category_id)
    {
        $this->partnerId = $category_id;
        return $this;
    }

    public function getPartnerId()
    {
        return $this->partnerId;
    }

    public function getFactoryName()
    {
        return CacheName::TRENDING_PRODUCTS;
    }
}
