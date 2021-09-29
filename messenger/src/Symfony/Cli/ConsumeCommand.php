<?php

namespace Emil\MessengerBundle\Cli;

use Emil\Messenger\Consumer\AmqpConsumer;
use Emil\Messenger\Consumer\CommandHandlerInterface;
use Emil\Messenger\Consumer\QueryHandlerInterface;
use Enqueue\Consumption\ChainExtension;
use Enqueue\Consumption\Extension\ExitStatusExtension;
use Enqueue\Consumption\Extension\LoggerExtension;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsumeCommand extends Command
{
    private string $amqpDSN;

    /** @var QueryHandlerInterface[] */
    private array $queryHandlers;

    /** @var CommandHandlerInterface[] */
    private array $commandHandlers;

    private array $eventHandlers;

    /**
     * @param QueryHandlerInterface[] $queryHandlers
     * @param CommandHandlerInterface[] $commandHandlers
     * @param array $eventHandlers
     */
    public function __construct(string $amqpDSN, array $queryHandlers = [], array $commandHandlers = [], array $eventHandlers = [])
    {
        parent::__construct();

        $this->amqpDSN = $amqpDSN;
        $this->queryHandlers = $queryHandlers;
        $this->commandHandlers = $commandHandlers;
        $this->eventHandlers = $eventHandlers;
    }

    protected function configure(): void
    {
        $this
            ->setName('emil:messenger:consume')
            ->setDescription('A worker that processes commands, queries and events');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consumer = new AmqpConsumer($this->amqpDSN, $this->commandHandlers, $this->queryHandlers);

        $runtimeExtensionChain = new LoggerExtension(new ConsoleLogger($output));
        $exitStatusExtension = new ExitStatusExtension();

        $consumer->consume(new ChainExtension([$runtimeExtensionChain, $exitStatusExtension]));

        return $exitStatusExtension->getExitStatus() ?? 0;
    }
}