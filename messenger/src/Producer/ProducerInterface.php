<?php

namespace Emil\Messenger\Producer;

use Emil\Domain\Message\CommandInterface;
use Emil\Domain\Message\QueryInterface;
use Enqueue\Rpc\Promise;

interface ProducerInterface
{
    public function sendCommand(CommandInterface $command): void;
    public function sendQuery(QueryInterface $query): Promise;
    // TODO sendEvent(EventInterface $event): void;
}