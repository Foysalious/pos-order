<?php

namespace App\Jobs;

use App\Services\Order\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class TrendingProductsCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        /** @var OrderService $service */
        $service = app(OrderService::class);
        $products = $service->getTrendingProducts($this->partnerId);
        $key = "trending_products_{$this->partnerId}";
        Cache::put($key, $products, now()->addHours(24));
    }
}
