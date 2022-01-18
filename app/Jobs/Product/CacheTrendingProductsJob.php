<?php

namespace App\Jobs\Product;

use App\Jobs\Job;
use App\Services\Cache\CacheAside;
use App\Services\Cache\Product\Trending\TrendingCacheRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use function app;


class CacheTrendingProductsJob extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    private int $partnerId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $partner_id)
    {
        $this->partnerId = $partner_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->attempts() > 2) return;
        /** @var TrendingCacheRequest $trending_cache_request */
        $trending_cache_request = app(TrendingCacheRequest::class);
        $trending_cache_request->setPartnerId($this->partnerId);
        /** @var CacheAside $cache_aside */
        $cache_aside = app(CacheAside::class);
        $cache_aside->setCacheRequest($trending_cache_request);
        $cache_aside->regenerateEntity();
    }
}
