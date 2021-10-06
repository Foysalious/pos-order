<?php namespace App\Jobs;

use App\Services\Webstore\SettingsSync\WebStoreSettingsSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WebStoreSettingsSyncJob implements ShouldQueue
{
    protected string $type;
    protected int $typeId;
    protected int $partnerId;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;



    public function __construct(int $partner_id, string $type, int $type_id)
    {
        $this->type = $type;
        $this->typeId = $type_id;
        $this->partnerId = $partner_id;
    }

    public function handle()
    {
        /** @var WebStoreSettingsSyncService $service */
        $service = app(WebStoreSettingsSyncService::class);
        $service->setPartner($this->partnerId)->setType($this->type)->setTypeId($this->typeId)->sync();
    }

}
