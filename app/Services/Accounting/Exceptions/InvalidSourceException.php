<?php namespace App\Services\Accounting\Exceptions;

use Exception;
use Throwable;

class InvalidSourceException extends Exception
{
    public function __construct($message = "Invalid or Empty Source Provided", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
