<?php

namespace Emil\Messenger\Consumer;

use Emil\Domain\Message\CommandInterface;

interface CommandHandlerInterface
{
    public function __invoke(CommandInterface $command): void;
    public static function getCommandName(): string;
}