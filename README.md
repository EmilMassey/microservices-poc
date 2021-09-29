## How to run

```bash
# for each composer.json run
composer install --ignore-platform-reqs

docker-compose up -d

# run consumers (-vvv to enable very verbose mode)
docker-compose exec address-php emil:messenger:consume -vvv
docker-compose exec user-php mil:messenger:consume -vvv
```

## Requests
### GET /users
Gets all the users
### GET /users/{id}
Gets one user by id
### POST /users
Create new user
#### Request body
```json
{
    "username": "johndoe",
    "street": "Krzywoustego 3",
    "city": "Poznań"
}
```

## Commands
### Dispatching command
```php
private ProducerInterface $producer;

public function __construct(ProducerInterface $producer)
{
    $this->producer = $producer;
}

public function createUser(CreateUserDto $input): Response
{
    $this->producer->sendCommand($input->toCommand()); // fire and forget (don't wait for the response)
    
    return new Response(null, RESPONSE::HTTP_NO_CONTENT);
}
```

### Handling
```php
final class CreateUserHandler implements CommandHandlerInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /** @param CreateUser $command */
    public function __invoke(CommandInterface $command): void
    {
        $user = new User($command->id(), $command->username());
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public static function getCommandName(): string
    {
        return CreateUser::class;
    }
}
```

## Querying
### Dispatching query

```php
private ProducerInterface $producer;

public function __construct(ProducerInterface $producer)
{
    $this->producer = $producer;
}

public function getUser(string $id): Response
{
    $promise = $this->producer->sendQuery(new GetUserQuery($id));
    $replyMessage = $promise->receive();

    if ($replyMessage->getProperty('error', false) === true) {
        return new JsonResponse($replyMessage->getBody(), $replyMessage->getProperty('http_code', 500));
    }

    return new JsonResponse($replyMessage->getBody(), Response::HTTP_OK, [], true);
}
```

### Handling
```php
class GetUserQueryHandler implements QueryHandlerInterface
{
    private UserRepositoryInterface $repository;
    private SerializerInterface $serializer;

    public function __construct(UserRepositoryInterface $repository, SerializerInterface $serializer)
    {
        $this->repository = $repository;
        $this->serializer = $serializer;
    }

    /** @param GetUserQuery $query */
    public function __invoke(QueryInterface $query): QueryResponseInterface
    {
        $user = $this->repository->find($query->userId());

        if ($user === null) {
            return ErrorQueryResponse::fromException(new ResourceNotFoundException("User {$query->userId()} not found"));
        }

        return new QueryResponse($this->serializer->serialize($user, 'json'));
    }

    public static function getQueryName(): string
    {
        return GetUserQuery::class;
    }
}
```

## TODO

`ProducerInterface` will also contain `sendEvent(EventInterface $event): void` method.
