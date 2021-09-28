<?php

namespace Emil\Domain\Message;

final class CreateAddress implements CommandInterface, \JsonSerializable
{
    private string $id;
    private string $street;
    private string $city;

    public function __construct(string $id, string $street, string $city)
    {
        $this->id = $id;
        $this->street = $street;
        $this->city = $city;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function street(): string
    {
        return $this->street;
    }

    public function city(): string
    {
        return $this->city;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'street' => $this->street,
            'city' => $this->city,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}