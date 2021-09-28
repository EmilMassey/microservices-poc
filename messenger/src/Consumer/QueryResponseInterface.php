<?php

namespace Emil\Messenger\Consumer;

interface QueryResponseInterface
{
    public function getBody(): string;
    public function isError(): bool;
    public function getErrorHttpCode(): ?int;
}