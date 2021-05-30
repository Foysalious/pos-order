<?php


namespace App\Services\Order\Constants;


use App\Helper\ConstGetter;

class OrderTypes
{
    use ConstGetter;
    const NEW = 'new';
    const RUNNING = 'running';
    const COMPLETED = 'completed';
}
