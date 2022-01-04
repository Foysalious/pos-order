<?php namespace App\Services\Cache;


use App\Services\Cache\Product\Trending\TrendingCacheFactory;

class CacheFactoryConfigurator
{
    /**
     * @param $name
     * @return CacheFactory
     */
    public function getFactory($name)
    {
        if ($name == CacheName::TRENDING_PRODUCTS) return new TrendingCacheFactory();
    }
}
