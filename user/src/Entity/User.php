<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;

/**
 * @Entity
 * @ORM\Table("app_user")
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\Column()
     */
    private string $id;

    /** @ORM\Column() */
    private string $username;

    /** @ORM\Column() */
    private string $addressId;

    public function __construct(string $id, string $username, string $addressId)
    {
        $this->id = $id;
        $this->username = $username;
        $this->addressId = $addressId;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getAddressId(): string
    {
        return $this->addressId;
    }

    public function setAddressId(string $addressId): void
    {
        $this->addressId = $addressId;
    }
}