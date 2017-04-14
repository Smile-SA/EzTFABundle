<?php

namespace Smile\EzTFABundle;

use Smile\EzTFABundle\DependencyInjection\Compiler\ProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SmileEzTFABundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ProviderPass());
    }
}
