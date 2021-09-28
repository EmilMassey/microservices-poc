<?php

namespace App\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Emil\Domain\Message\GetAddressQuery;
use Emil\Domain\Message\GetUsersQuery;
use Emil\Domain\Message\QueryInterface;
use Emil\Domain\Model\Address;
use Emil\Domain\Model\User;
use Emil\Messenger\Consumer\QueryHandlerInterface;
use Emil\Messenger\Consumer\QueryResponse;
use Emil\Messenger\Consumer\QueryResponseInterface;
use Emil\Messenger\Producer\ProducerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class GetUsersQueryHandler implements QueryHandlerInterface
{
    private ProducerInterface $producer;
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;

    public function __construct(ProducerInterface $producer, EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->producer = $producer;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function __invoke(QueryInterface $query): QueryResponseInterface
    {
        return new QueryResponse($this->serializer->serialize($this->getUsers(), 'json'));
    }

    public static function getQueryName(): string
    {
        return GetUsersQuery::class;
    }

    /** @return User[] */
    public function getUsers(): array
    {
        $repository = $this->entityManager->getRepository(\App\Entity\User::class);
        /** @var \App\Entity\User[] $user */
        $users = $repository->findAll();

        return array_map([$this, 'convertUserToModel'], $users);
    }

    private function convertUserToModel(\App\Entity\User $user): User
    {
        return new User($user->getId(), $user->getUsername(), $this->getAddress($user->getAddressId()));
    }

    private function getAddress(string $id): Address
    {
        $promise = $this->producer->sendQuery(new GetAddressQuery($id));
        $replyMessage = $promise->receive(2000);

        if ($replyMessage->getProperty('error', false) === true) {
            throw new \RuntimeException("Could not get Address $id");
        }

        return $this->serializer->deserialize($replyMessage->getBody(), Address::class, 'json');
    }
}