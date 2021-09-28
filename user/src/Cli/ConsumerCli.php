<?php

namespace App\Cli;

use App\MessageHandler\CreateUserHandler;
use App\MessageHandler\GetUserQueryHandler;
use App\MessageHandler\GetUsersQueryHandler;
use Emil\Messenger\Consumer\AmqpConsumer;
use Enqueue\Consumption\ChainExtension;
use Enqueue\Consumption\Extension\ExitStatusExtension;
use Enqueue\Consumption\Extension\LoggerExtension;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsumerCli extends Command
{
    public static $defaultName = 'app:consume';
    private string $amqpDsn;
    private GetUsersQueryHandler $getUsersQueryHandler;
    private GetUserQueryHandler $getUserQueryHandler;

    // TODO autowiring all handlers as array
    private CreateUserHandler $createUserHandler;

    public function __construct(string $amqpDsn, GetUsersQueryHandler $getUsersQueryHandler, GetUserQueryHandler $getUserQueryHandler, CreateUserHandler $createUserHandler)
    {
        parent::__construct();
        $this->amqpDsn = $amqpDsn;
        $this->getUsersQueryHandler = $getUsersQueryHandler;
        $this->getUserQueryHandler = $getUserQueryHandler;
        $this->createUserHandler = $createUserHandler;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $consumer = new AmqpConsumer($this->amqpDsn, [$this->createUserHandler], [$this->getUsersQueryHandler, $this->getUserQueryHandler]);

        $runtimeExtensionChain = new LoggerExtension(new ConsoleLogger($output));
        $exitStatusExtension = new ExitStatusExtension();

        $consumer->consume(new ChainExtension([$runtimeExtensionChain, $exitStatusExtension]));

        return $exitStatusExtension->getExitStatus() ?? 0;
    }
}