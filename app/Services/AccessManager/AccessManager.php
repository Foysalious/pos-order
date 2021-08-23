<?php namespace App\Services\AccessManager;


use App\Exceptions\AuthorizationException;
use App\Services\APIServerClient\ApiServerClient;

class AccessManager
{
    protected int $partnerId;
    protected string $feature;
    protected ?int $product_published_count;

    public function __construct(
        protected ApiServerClient $apiServerClient
    ){}

    /**
     * @param int $partnerId
     * @return AccessManager
     */
    public function setPartnerId(int $partnerId): AccessManager
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    /**
     * @param string $feature
     * @return AccessManager
     */
    public function setFeature(string $feature): AccessManager
    {
        $this->feature = $feature;
        return $this;
    }

    /**
     * @param int|null $product_published_count
     * @return AccessManager
     */
    public function setProductPublishedCount(?int $product_published_count): AccessManager
    {
        $this->product_published_count = $product_published_count;
        return $this;
    }

    private function makeData()
    {
        return [
            'partner_id' => $this->partnerId,
            'feature' => $this->feature,
            'product_published_count' => $this->product_published_count
        ];
    }

    /**
     * @throws AuthorizationException
     */
    public function checkAccess(): bool
    {
        $response = $this->apiServerClient->setBaseUrl()->post( 'pos/v1/check-access', $this->makeData());
        if ($response["code"] !== 200) throw new AuthorizationException($response["message"], $response["code"]);
        return true;
    }

}
