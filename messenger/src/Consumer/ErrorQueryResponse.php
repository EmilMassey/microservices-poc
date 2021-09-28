<?php

namespace Emil\Messenger\Consumer;

use Emil\Domain\Exception\ResourceNotFoundException;
use Webmozart\Assert\InvalidArgumentException;

final class ErrorQueryResponse extends QueryResponse
{
    public static function fromException(\Exception $exception): self
    {
        $httpCode = match ($exception::class) {
            ResourceNotFoundException::class => 404,
            InvalidArgumentException::class => 400,
            default => 500,
        };

        return new self($exception->getMessage(), $httpCode);
    }

    private function __construct(string $message, int $httpCode)
    {
        return parent::__construct($message, true, $httpCode);
    }
}