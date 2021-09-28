<?php

namespace Emil\Domain\Message;

final class GetUserQuery implements \JsonSerializable, QueryInterface
{
    private string $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function toArray(): array
    {
        return ['userId' => $this->userId];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}