<?php

namespace Emil\Domain\Message;

final class GetAddressQuery implements \JsonSerializable, QueryInterface
{
    private string $addressId;

    public function __construct(string $addressId)
    {
        $this->addressId = $addressId;
    }

    public function addressId(): string
    {
        return $this->addressId;
    }

    public function toArray(): array
    {
        return ['addressId' => $this->addressId];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}