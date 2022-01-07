<?php namespace App\Services\Accounting;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use App\Services\Accounting\Exceptions\AccountingEntryServerError;

class AccountingEntryClient
{
    /** @var Client $client */
    protected Client $client;
    protected string $baseUrl;
    protected string $apiKey;
    protected string $userType;
    protected int $userId;
    protected $reportType;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = rtrim(config('accounting.api_url'), '/');
        $this->apiKey = config('accounting.api_key');
    }

    /**
     * @param $uri
     * @param null $data
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function get($uri, $data = null)
    {
        return $this->call('get', $uri, $data);
    }

    /**
     * @param $uri
     * @param $data
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function post($uri, $data)
    {
        return $this->call('post', $uri, $data);
    }

    /**
     * @param $uri
     * @param $data
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function put($uri, $data)
    {
        return $this->call('put', $uri, $data);
    }

    /**
     * @param $uri
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function delete($uri)
    {
        return $this->call('delete', $uri);
    }


    /**
     * @param      $method
     * @param      $uri
     * @param null $data
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function call($method, $uri, $data = null, $multipart = false)
    {
        if (!$this->userType || !$this->userId ) {
            throw new AccountingEntryServerError('Set user type and user id', 0);
        }
        $res = decodeGuzzleResponse(
            $this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data))
        );
        if ($res['code'] != 200) {
            throw new AccountingEntryServerError($res['message']);
        }
        return $res['data'] ?? $res['message'];
    }

    /**
     * @param $uri
     * @return string
     */
    public function makeUrl($uri)
    {
        return $this->baseUrl . "/" . $uri;
    }

    /**
     * @param null $data
     * @return array
     */
    public function getOptions($data = null)
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
    public function setUserType($userType)
    {
        $this->userType = $userType;
        return $this;
    }


    /**
     * @param $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }
}
