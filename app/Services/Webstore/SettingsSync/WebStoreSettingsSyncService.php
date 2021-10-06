<?php namespace App\Services\Webstore\SettingsSync;

use App\Services\ClientServer\SmanagerSettings\SmanagerSettingsServerClient;

class WebStoreSettingsSyncService
{

    protected string $type;
    protected int $typeId;
    protected int $partnerId;

    public function __construct(
        protected SmanagerSettingsServerClient $client
    )
    {
    }

    /**
     * @param string $type
     * @return WebStoreSettingsSyncService
     */
    public function setType(string $type): WebStoreSettingsSyncService
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param int $type_id
     * @return WebStoreSettingsSyncService
     */
    public function setTypeId(int $type_id): WebStoreSettingsSyncService
    {
        $this->typeId = $type_id;
        return $this;
    }

    /**
     * @param int $partner_id
     * @return WebStoreSettingsSyncService
     */
    public function setPartner(int $partner_id): WebStoreSettingsSyncService
    {
        $this->partnerId = $partner_id;
        return $this;
    }

    public function sync() {
        $uri = $this->getUri();
        $data = $this->makeData();
        $this->client->setBaseUrl()->post($uri,$data);
    }

    private function getUri() : string
    {
        return 'api/v1/partners/' . $this->partnerId . '/theme-synchronization';
    }

    private function makeData()
    {
        return [
            'type' => $this->type,
            'type_id' => $this->typeId,
        ];
    }

}
