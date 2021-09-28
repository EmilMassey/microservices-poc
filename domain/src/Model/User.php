<?php

namespace Emil\Domain\Model;

class User
{
    private string $id;
    private string $username;
    private Address $address;

    public function __construct(string $id, string $username, Address $address)
    {
        $this->id = $id;
        $this->username = $username;
        $this->address = $address;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }
}