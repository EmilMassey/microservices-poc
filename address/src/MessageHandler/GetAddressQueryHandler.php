<?php

namespace App\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Emil\Domain\Exception\ResourceNotFoundException;
use Emil\Domain\Message\GetAddressQuery;
use Emil\Domain\Message\GetUsersQuery;
use Emil\Domain\Message\QueryInterface;
use Emil\Domain\Model\Address;
use Emil\Messenger\Consumer\ErrorQueryResponse;
use Emil\Messenger\Consumer\QueryHandlerInterface;
use Emil\Messenger\Consumer\QueryResponse;
use Emil\Messenger\Consumer\QueryResponseInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class GetAddressQueryHandler implements QueryHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    /** @param GetAddressQuery $query */
    public function __invoke(QueryInterface $query): QueryResponseInterface
    {
        $repository = $this->entityManager->getRepository(\App\Entity\Address::class);
        /** @var \App\Entity\Address|null $address */
        $address = $repository->find($query->addressId());

        if ($address === null) {
            return ErrorQueryResponse::fromException(new ResourceNotFoundException("Address {$query->addressId()} not found"));
        }

        return new QueryResponse($this->serializer->serialize(new Address($address->getId(), $address->getStreet(), $address->getCity()), 'json'));
    }

    public static function getQueryName(): string
    {
        return GetAddressQuery::class;
    }
}