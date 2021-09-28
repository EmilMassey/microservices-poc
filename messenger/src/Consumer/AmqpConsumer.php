<?php

namespace Emil\Messenger\Consumer;

use Enqueue\AmqpExt\AmqpConnectionFactory;
use Enqueue\AmqpExt\AmqpContext;
use Enqueue\Consumption\ChainExtension;
use Enqueue\Consumption\Extension\LogExtension;
use Enqueue\Consumption\Extension\ReplyExtension;
use Enqueue\Consumption\ExtensionInterface;
use Enqueue\Consumption\QueueConsumer;
use Interop\Amqp\Impl\AmqpQueue;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class AmqpConsumer implements ConsumerInterface
{
    private AmqpContext $context;
    /** @var CommandHandlerInterface[] */
    private array $commandHandlers;
    /** @var QueryHandlerInterface[] */
    private array $queryHandlers;
    private Serializer $serializer;

    /**
     * @param CommandHandlerInterface[] $commandHandlers
     * @param QueryHandlerInterface[] $queryHandlers
     */
    public function __construct(string $amqpDsn, array $commandHandlers = [], array $queryHandlers = [])
    {
        $this->context = (new AmqpConnectionFactory($amqpDsn))->createContext();
        $this->serializer = new Serializer([new JsonSerializableNormalizer(), new ObjectNormalizer(), new ArrayDenormalizer()], [new JsonEncoder()]);
        $this->commandHandlers = $commandHandlers;
        $this->queryHandlers = $queryHandlers;
    }

    public function consume(ExtensionInterface $runtimeExtension = null): void
    {
        $consumer = new QueueConsumer($this->context, new ChainExtension([new LogExtension(), new ReplyExtension()]));

        foreach ($this->commandHandlers as $commandHandler) {
            $this->context->declareQueue(new AmqpQueue($commandHandler::getCommandName()));

            $consumer->bind(
                $commandHandler::getCommandName(),
                new CommandProcessor(
                    $commandHandler::getCommandName(),
                    $commandHandler,
                    $this->serializer
                )
            );
        }

        foreach ($this->queryHandlers as $queryHandler) {
            $this->context->declareQueue(new AmqpQueue($queryHandler::getQueryName()));

            $consumer->bind(
                $queryHandler::getQueryName(),
                new QueryProcessor(
                    $queryHandler::getQueryName(),
                    $queryHandler,
                    $this->serializer
                )
            );
        }

        $consumer->consume($runtimeExtension);
    }
}