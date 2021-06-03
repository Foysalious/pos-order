<?php namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class OrderException extends BaseException
{
    public function __construct($message = 'Option Not Found', $code = Response::HTTP_NOT_FOUND, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
