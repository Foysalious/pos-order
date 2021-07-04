<?php namespace App\Services\ClientServer;


use App\Services\ClientServer\Exceptions\BaseClientServerError;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

abstract class BaseClientServer implements BaseClientServerInterface
{
    protected Client $client;
    protected string $baseUrl;
    protected string $header;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function setHeader($header)
    {
        $this->header = $header;
        return $this;
    }

    abstract public function setBaseUrl();

    public function get($uri)
    {
        return $this->call('get', $uri);
    }

    public function call($method, $uri, $data = null, $multipart = false)
    {
        try {
            return json_decode($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data, $multipart))->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            $res = $e->getResponse();
            $http_code = $res->getStatusCode();
            $message = $res->getBody()->getContents();
            if ($http_code > 399 && $http_code < 500) throw new BaseClientServerError($message, $http_code);
            throw new BaseClientServerError($e->getMessage(), $http_code);
        }
    }

    private function makeUrl($uri) : string
    {
        return $this->baseUrl . "/" . $uri;
    }

    private function getOptions($data = null, $multipart = false)
    {
        $options['headers'] = [
            'Accept' => 'application/json'
        ];
        if (isset($this->header))  $options['headers'] += ['Authorization' => $this->header];
        if (!$data) return $options;
        if ($multipart) {
            $options['multipart'] = $data;
        } else {
            $options['form_params'] = $data;
            $options['json'] = $data;
        }
        return $options;
    }

    public function post($uri, $data, $multipart = false)
    {
        return $this->call('post', $uri, $data, $multipart);
    }

    /**
     * @param $uri
     * @param $data
     * @param bool $multipart
     * @return array|object|string|null
     * @throws BaseClientServerError
     */
    public function put($uri, $data, $multipart = false)
    {
        return $this->call('put', $uri, $data, $multipart);
    }

    /**
     * @param $uri
     * @return array|object|string|null
     * @throws BaseClientServerError
     */
    public function delete($uri)
    {
        return $this->call('DELETE', $uri);
    }
}
