<?php namespace App\Services\ClientServer;


interface BaseClientServerInterface
{
    public function get($uri);
    public function call($method, $uri, $data = null, $multipart = false);
    public function post($uri, $data, $multipart = false);
    public function put($uri, $data, $multipart = false);
    public function delete($uri);
}
