<?php

namespace Emil\MessengerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class AttachHandlersToCliCommandPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $cliDefinition = $container->findDefinition('emil.messenger.cli.consume_command');
        $commandHandlers = array_keys($container->findTaggedServiceIds('emil.messenger.command_handler'));
        $queryHandlers = array_keys($container->findTaggedServiceIds('emil.messenger.query_handler'));

        $cliDefinition->setArgument('$commandHandlers', array_map(function (string $id): Reference {
            return new Reference($id);
        }, $commandHandlers));

        $cliDefinition->setArgument('$queryHandlers', array_map(function (string $id): Reference {
            return new Reference($id);
        }, $queryHandlers));
    }
}