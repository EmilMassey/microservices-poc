<?php

namespace Emil\Messenger\Producer;

use Emil\Domain\Message\CommandInterface;
use Emil\Domain\Message\QueryInterface;
use Enqueue\AmqpExt\AmqpConnectionFactory;
use Enqueue\AmqpExt\AmqpContext;
use Enqueue\Client\Driver\AmqpDriver;
use Enqueue\Rpc\Promise;
use Enqueue\Rpc\RpcClient;
use Enqueue\SimpleClient\SimpleClient;
use Interop\Queue\Context;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class AmqpProducer implements ProducerInterface
{
    const DEFAULT_QUERY_TIMEOUT = 60000;

    private AmqpContext $context;
    private SerializerInterface $serializer;
    private RpcClient $rpcClient;
    private int $defaultQueryTimeout;

    public function __construct(string $amqpDsn, int $defaultQueryTimeout = self::DEFAULT_QUERY_TIMEOUT)
    {
        $this->context = (new AmqpConnectionFactory($amqpDsn))->createContext();
        $this->serializer = new Serializer([new JsonSerializableNormalizer(), new ObjectNormalizer(), new ArrayDenormalizer()], [new JsonEncoder()]);
        $this->defaultQueryTimeout = $defaultQueryTimeout;
        $this->rpcClient = new RpcClient($this->context);
    }

    public function sendCommand(CommandInterface $command): void
    {
        $queue = $this->context->createQueue($command::class);
        $this->context->declareQueue($queue);

        $this->context->createProducer()->send(
            $queue,
            $this->context->createMessage($this->serializer->serialize($command, 'json'))
        );
    }

    public function sendQuery(QueryInterface $query): Promise
    {
        $queue = $this->context->createQueue($query::class);
        $this->context->declareQueue($queue);

        return $this->rpcClient->callAsync(
            $queue,
            $this->context->createMessage($this->serializer->serialize($query, 'json')),
            $this->defaultQueryTimeout
        );
    }
}