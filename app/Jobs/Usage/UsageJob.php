<?php namespace App\Jobs\Usage;

use App\Services\Usage\UsageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UsageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $partnerId;
    private string $usageType;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $partnerId, string $usageType)
    {
        $this->partnerId = $partnerId;
        $this->usageType = $usageType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $usageService = app(UsageService::class);
        $usageService->setUserId($this->partnerId)->setUsageType($this->usageType)->store();
    }
}
