<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;

/** @Entity */
class Address
{
    /**
     * @ORM\Id()
     * @ORM\Column()
     */
    private string $id;

    /** @ORM\Column() */
    private string $street;

    /** @ORM\Column() */
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

    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }
}