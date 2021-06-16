<?php namespace App\Services\Accounting\Exceptions;

use Exception;
use Throwable;

class KeyNotFoundException extends Exception
{
    public function __construct($message = "Invalid or Empty Key Provided", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
