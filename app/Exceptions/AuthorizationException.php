<?php namespace App\Exceptions;


use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthorizationException extends BaseException
{
    public function __construct($message = 'Not allowed to perform this action', $code = Response::HTTP_FORBIDDEN, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
