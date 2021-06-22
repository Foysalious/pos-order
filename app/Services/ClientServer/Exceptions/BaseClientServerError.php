<?php namespace App\Services\ClientServer\Exceptions;


use App\Exceptions\HttpException;
use Throwable;

class BaseClientServerError extends HttpException
{
    public function __construct($message = "", $code = 402, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'Service server not working as expected.';
        }
        parent::__construct($message, $code, $previous);
    }
}
