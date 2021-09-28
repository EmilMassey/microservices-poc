<?php

namespace Emil\Messenger\Consumer;

use Enqueue\Consumption\ExtensionInterface;

interface ConsumerInterface
{
    public function consume(ExtensionInterface $runtimeExtension = null): void;
}