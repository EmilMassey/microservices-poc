<?php

namespace Emil\Messenger\Consumer;

use Emil\Domain\Message\QueryInterface;

interface QueryHandlerInterface
{
    public function __invoke(QueryInterface $query): QueryResponseInterface;
    public static function getQueryName(): string;
}