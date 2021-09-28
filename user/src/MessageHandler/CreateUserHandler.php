<?php

namespace App\MessageHandler;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Emil\Domain\Message\CommandInterface;
use Emil\Domain\Message\CreateAddress;
use Emil\Domain\Message\CreateUser;
use Emil\Messenger\Consumer\CommandHandlerInterface;
use Emil\Messenger\Producer\ProducerInterface;
use Ramsey\Uuid\Uuid;

final class CreateUserHandler implements CommandHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private ProducerInterface $producer;

    public function __construct(EntityManagerInterface $entityManager, ProducerInterface $producer)
    {
        $this->entityManager = $entityManager;
        $this->producer = $producer;
    }

    /** @param CreateUser $command */
    public function __invoke(CommandInterface $command): void
    {
        $addressId = Uuid::uuid4()->toString();
        $this->producer->sendCommand(new CreateAddress($addressId, 'lorem', 'ipsum'));

        $user = new User($command->id(), 'username', $addressId);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public static function getCommandName(): string
    {
        return CreateUser::class;
    }
}