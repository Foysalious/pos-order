<?php namespace App\Services\Accounting;

use App\Models\EventNotification;
use App\Services\ClientServer\BaseClientServer;
use App\Services\EventNotification\Request;
use App\Services\EventNotification\Statuses;
use GuzzleHttp\Client;
use App\Services\Accounting\Exceptions\AccountingEntryServerError;
use GuzzleHttp\Exception\GuzzleException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class AccountingEntryClient extends BaseClientServer
{
    /** @var Client $client */
    protected Client $client;
    protected string $baseUrl;
    protected string $apiKey;
    protected string $userType;
    protected int $userId;
    private EventNotification $eventNotification;

    /**
     * @param EventNotification $eventNotification
     * @return AccountingEntryClient
     */
    public function setEventNotification(EventNotification $eventNotification): AccountingEntryClient
    {
        $this->eventNotification = $eventNotification;
        return $this;
    }

    public function __construct(Client $client)
    {
        parent::__construct($client);
        $this->apiKey = config('accounting.api_key');
    }

    /**
     * @param      $method
     * @param      $uri
     * @param null $data
     * @param bool $multipart
     * @return mixed
     * @throws AccountingEntryServerError
     * @throws GuzzleException
     * @throws UnknownProperties
     */
    public function call($method, $uri, $data = null, $multipart = false): mixed
    {
        try {
            if (!$this->userType || !$this->userId || !$this->eventNotification) throw new AccountingEntryServerError('User type and user id not set');
            $url = $this->makeUrl($uri);
            $method = strtoupper($method);
            $options = $this->getOptions($data);
            $request = new Request([
                'url' => $url,
                'method' => $method,
                'json' => $options['json'] ?? null
            ]);
            $this->eventNotification->update(['request' => json_encode($request->toArray())]);
            $res = decodeGuzzleResponse($this->client->request($method, $url, $options));
            $this->eventNotification->update(['response' => json_encode($res), 'status' => $res['code'] != 200 ? Statuses::FAILED : Statuses::SUCCESS]);
            if ($res['code'] != 200) throw new AccountingEntryServerError($res['message']);
            return $res['data'] ?? $res['message'];
        } catch (GuzzleException $exception) {
            $this->eventNotification->update(['response' => json_encode(['message' => $exception->getMessage(), 'code' => $exception->getCode()]), 'status' => Statuses::FAILED]);
            throw $exception;
        }
    }

    /**
     * @param $uri
     * @return string
     */
    public function makeUrl($uri): string
    {
        return $this->getBaseUrl() . "/" . $uri;
    }

    /**
     * @param null $data
     * @return array
     */
    public function getOptions($data = null): array
    {
        $options['headers'] = [
            'Content-Type' => 'application/json',
            'x-api-key' => $this->apiKey,
            'Accept' => 'application/json',
            'Ref-Id' => $this->userId,
            'Ref-Type' => $this->userType
        ];
        if ($data) {
            $options['json'] = $data;
        }
        return $options;
    }


    /**
     * @param $userType
     * @return $this
     */
    public function setUserType($userType): static
    {
        $this->userType = $userType;
        return $this;
    }


    /**
     * @param $userId
     * @return $this
     */
    public function setUserId($userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    public function getBaseUrl(): string
    {
        return rtrim(config('accounting.api_url'), '/');
    }
}
