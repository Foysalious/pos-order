<?php

namespace App\Services\EventNotification;

use Spatie\DataTransferObject\DataTransferObject;

class Response extends DataTransferObject
{
    public string $httpCode;
    public ?string $message;
    public string $data;
}
