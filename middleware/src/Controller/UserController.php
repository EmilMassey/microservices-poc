<?php

namespace App\Controller;

use App\Dto\CreateUserDto;
use Emil\Domain\Message\CreateUser;
use Emil\Domain\Message\GetUserQuery;
use Emil\Domain\Message\GetUsersQuery;
use Emil\Messenger\Producer\ProducerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    private ProducerInterface $producer;
    private SerializerInterface $serializer;

    public function __construct(ProducerInterface $producer, SerializerInterface $serializer)
    {
        $this->producer = $producer;
        $this->serializer = $serializer;
    }

    public function index(): Response
    {
        $promise = $this->producer->sendQuery(new GetUsersQuery());
        $replyMessage = $promise->receive();

        return new JsonResponse($replyMessage->getBody(), Response::HTTP_OK, [], true);
    }

    public function getOne(string $id): Response
    {
        $promise = $this->producer->sendQuery(new GetUserQuery($id));
        $replyMessage = $promise->receive();

        if ($replyMessage->getProperty('error', false) === true) {
            return new JsonResponse($replyMessage->getBody(), $replyMessage->getProperty('http_code', 500));
        }

        return new JsonResponse($replyMessage->getBody(), Response::HTTP_OK, [], true);
    }

    public function create(Request $request): Response
    {
        $userId = Uuid::uuid4()->toString();
        /** @var CreateUserDto $input */
        $input = $this->serializer->deserialize($request->getContent(), CreateUserDto::class, 'json');

        $command = new CreateUser($userId, $input->username, $input->street, $input->city);
        $this->producer->sendCommand($command);

        return $this->redirectToRoute('get_one', ['id' => $userId]);
    }
}