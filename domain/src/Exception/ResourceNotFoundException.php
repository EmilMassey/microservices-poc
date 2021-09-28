<?php

namespace Emil\Domain\Exception;

class ResourceNotFoundException extends \RuntimeException
{
    public function __construct(string $message = 'Resource not found', int $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}