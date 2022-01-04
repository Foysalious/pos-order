<?php namespace App\Services\Cache;

use App\Services\Cache\Exceptions\CacheGenerationException;

interface DataStoreObject
{
    public function setCacheRequest(CacheRequest $cache_request);

    /**
     * @return array|null
     * @throws CacheGenerationException
     */
    public function generate();
}
