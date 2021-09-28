<?php

namespace Emil\Messenger\Consumer;

use Emil\Domain\Message\CommandInterface;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Symfony\Component\Serializer\SerializerInterface;

final class CommandProcessor implements Processor
{
    private string $commandName;
    private CommandHandlerInterface $handler;
    private SerializerInterface $serializer;

    public function __construct(string $commandName, CommandHandlerInterface $handler, SerializerInterface $serializer)
    {
        $this->commandName = $commandName;
        $this->handler = $handler;
        $this->serializer = $serializer;
    }

    public function process(Message $message, Context $context): string
    {
        $command = $this->serializer->deserialize($message->getBody(), $this->commandName, 'json');

        if (!$command instanceof CommandInterface) {
            // TODO log error

            return self::REJECT;
        }

        $this->handler->__invoke($command);

        return self::ACK;
    }
}