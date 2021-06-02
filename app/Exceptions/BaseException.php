<?php namespace App\Exceptions;

use Exception;
use Throwable;

class BaseException extends Exception
{
    public function __construct($message = "Something Went Wrong", $code = 500, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
