<?php

namespace Emil\Domain\Model;

class Address
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

    public function getId(): string
    {
        return $this->id;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getCity(): string
    {
        return $this->city;
    }
}