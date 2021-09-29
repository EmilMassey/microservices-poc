<?php

namespace Emil\MessengerBundle;

use Emil\MessengerBundle\DependencyInjection\Compiler\AttachHandlersToCliCommandPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EmilMessengerBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AttachHandlersToCliCommandPass());
    }
}