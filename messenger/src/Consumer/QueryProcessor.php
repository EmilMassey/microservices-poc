<?php

namespace Emil\Messenger\Consumer;

use Emil\Domain\Message\CommandInterface;
use Emil\Domain\Message\QueryInterface;
use Enqueue\Consumption\Result;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Symfony\Component\Serializer\SerializerInterface;

final class QueryProcessor implements Processor
{
    private string $queryName;
    private QueryHandlerInterface $handler;
    private SerializerInterface $serializer;

    public function __construct(string $queryName, QueryHandlerInterface $handler, SerializerInterface $serializer)
    {
        $this->queryName = $queryName;
        $this->handler = $handler;
        $this->serializer = $serializer;
    }

    public function process(Message $message, Context $context): Result
    {
        $query = $this->serializer->deserialize($message->getBody(), $this->queryName, 'json');

        if (!$query instanceof QueryInterface) {
            // TODO log error

            return Result::reject("Payload could not be serialized to {$this->queryName}");
        }

        $response = $this->handler->__invoke($query);
        $message = $context->createMessage($response->getBody());
        $message->setProperty('error', $response->isError());

        if ($response->isError()) {
            $message->setProperty('http_code', $response->getErrorHttpCode());
        }

        return Result::reply($message);
    }
}