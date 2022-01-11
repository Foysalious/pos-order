<?php namespace App\Services\EventNotification;

use App\Helper\ConstGetter;

class Statuses
{
    use ConstGetter;

    const PENDING = "pending";
    const SUCCESS = "success";
    const FAILED = "failed";
}
