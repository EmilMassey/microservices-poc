<?php

namespace Emil\Domain\Message;

final class CreateUser implements CommandInterface, \JsonSerializable
{
    private string $id;
    private string $username;
    private string $street;
    private string $city;

    public function __construct(string $id, string $username, string $street, string $city)
    {
        $this->id = $id;
        $this->username = $username;
        $this->street = $street;
        $this->city = $city;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function username(): string
    {
        return $this->username;
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
            'username' => $this->username,
            'street' => $this->street,
            'city' => $this->city,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}