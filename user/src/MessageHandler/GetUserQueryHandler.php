<?php

namespace App\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Emil\Domain\Exception\ResourceNotFoundException;
use Emil\Domain\Message\GetAddressQuery;
use Emil\Domain\Message\GetUserQuery;
use Emil\Domain\Message\QueryInterface;
use Emil\Domain\Model\Address;
use Emil\Domain\Model\User;
use Emil\Messenger\Consumer\ErrorQueryResponse;
use Emil\Messenger\Consumer\QueryHandlerInterface;
use Emil\Messenger\Consumer\QueryResponse;
use Emil\Messenger\Consumer\QueryResponseInterface;
use Emil\Messenger\Producer\ProducerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class GetUserQueryHandler implements QueryHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private ProducerInterface $producer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer, ProducerInterface $producer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->producer = $producer;
    }

    /** @param GetUserQuery $query */
    public function __invoke(QueryInterface $query): QueryResponseInterface
    {
        $repository = $this->entityManager->getRepository(\App\Entity\User::class);
        /** @var \App\Entity\User|null $user */
        $user = $repository->find($query->userId());

        if ($user === null) {
            return ErrorQueryResponse::fromException(new ResourceNotFoundException("User {$query->userId()} not found"));
        }

        return new QueryResponse($this->serializer->serialize($this->convertUserToModel($user), 'json'));
    }

    public static function getQueryName(): string
    {
        return GetUserQuery::class;
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