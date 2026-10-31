<?php
 
namespace App\Exceptions;
 
use RuntimeException;
use Throwable;
 
class ExternalServiceException extends RuntimeException
{
    public function __construct(
        public readonly string $service,
        string $message,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
