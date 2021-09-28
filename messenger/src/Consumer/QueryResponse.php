<?php

namespace Emil\Messenger\Consumer;

class QueryResponse implements QueryResponseInterface
{
    private string $body;
    private bool $error;
    private ?int $errorHttpCode;

    public function __construct(string $body, bool $error = false, int $errorHttpCode = null)
    {
        if (!$error && $errorHttpCode !== null) {
            throw new \LogicException('Cannot set http code if no error occurred');
        }

        $this->body = $body;
        $this->error = $error;
        $this->errorHttpCode = $errorHttpCode;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function isError(): bool
    {
        return $this->error;
    }

    public function getErrorHttpCode(): ?int
    {
        if (!$this->error) {
            throw new \LogicException('Cannot get error http code if no error occurred');
        }

        return $this->errorHttpCode;
    }
}