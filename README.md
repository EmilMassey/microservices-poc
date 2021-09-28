## How to run

```bash
# for each composer.json run
composer install --ignore-platform-reqs

docker-compose up -d

# run consumers (-vvv to enable very verbose mode)
docker-compose exec address-php app:consume -vvv
docker-compose exec user-php app:consume -vvv
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

## TODO
There will be messenger bundle to autoconfigure the library and provide CLI commands.
Microservices will not need to require enqueue dependencies and the only dependency needed will be
domain and messenger libraries.

`ProducerInterface` will also contain `sendEvent(EventInterface $event): void` method.

## Dispatching commands
```php
private ProducerInterface $producer;

public function __construct(ProducerInterface $producer)
{
    $this->producer = $producer;
}

public function createUser(CreateUser $command): Response
{
    $this->producer->sendCommand($command); // fire and forget (don't wait for the response)
    
    return new Response(null, RESPONSE::HTTP_NO_CONTENT);
}
```

## Querying
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