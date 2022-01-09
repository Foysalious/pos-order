<?php namespace App\Services\EventNotification;

use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class Request extends DataTransferObject
{
    public string $url;
    public string $method;
    public ?array $headers;
    public ?array $json;
}
