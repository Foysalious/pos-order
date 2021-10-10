<?php namespace App\Services\Usage;

use App\Services\APIServerClient\ApiServerClient;

class UsageService
{
    protected int $userId;
    protected string $usage_type;

    public function __construct(
        protected ApiServerClient $apiServerClient){}

    /**
     * @param int $userId
     * @return UsageService
     */
    public function setUserId(int $userId): UsageService
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @param string $usage_type
     * @return UsageService
     */
    public function setUsageType(string $usage_type): UsageService
    {
        $this->usage_type = $usage_type;
        return $this;
    }

    private function makeData(): array
    {
        return [
            'partner_id' => $this->userId,
            'usage_type' => $this->usage_type
        ];
    }

    public function store()
    {
        $this->apiServerClient->post( 'pos/v1/usages', $this->makeData());
    }
}
