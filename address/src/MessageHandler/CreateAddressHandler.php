<?php

namespace App\MessageHandler;

use App\Entity\Address;
use Doctrine\ORM\EntityManagerInterface;
use Emil\Domain\Message\CommandInterface;
use Emil\Domain\Message\CreateAddress;
use Emil\Messenger\Consumer\CommandHandlerInterface;

class CreateAddressHandler implements CommandHandlerInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /** @param CreateAddress $command */
    public function __invoke(CommandInterface $command): void
    {
        $address = new Address($command->id(), $command->street(), $command->city());

        $this->entityManager->persist($address);
        $this->entityManager->flush();
    }

    public static function getCommandName(): string
    {
        return CreateAddress::class;
    }
}