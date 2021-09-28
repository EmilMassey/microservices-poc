<?php

namespace App\Cli;

use App\MessageHandler\CreateAddressHandler;
use App\MessageHandler\GetAddressQueryHandler;
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
    private GetAddressQueryHandler $getAddressQueryHandler;

    // TODO autowiring all handlers as array
    private CreateAddressHandler $createAddressHandler;

    public function __construct(string $amqpDsn, GetAddressQueryHandler $getAddressQueryHandler, CreateAddressHandler $createAddressHandler)
    {
        parent::__construct();
        $this->amqpDsn = $amqpDsn;
        $this->getAddressQueryHandler = $getAddressQueryHandler;
        $this->createAddressHandler = $createAddressHandler;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $consumer = new AmqpConsumer($this->amqpDsn, [$this->createAddressHandler], [$this->getAddressQueryHandler]);

        $runtimeExtensionChain = new LoggerExtension(new ConsoleLogger($output));
        $exitStatusExtension = new ExitStatusExtension();

        $consumer->consume(new ChainExtension([$runtimeExtensionChain, $exitStatusExtension]));

        return $exitStatusExtension->getExitStatus() ?? 0;
    }
}