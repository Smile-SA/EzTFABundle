<?php

namespace Smile\EzTFABundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ProviderPass
 * @package Smile\EzTFABundle\DependencyInjection\Compiler
 */
class ProviderPass implements CompilerPassInterface
{
    /**
     * Process TFA providers
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('smileeztfa.security.auth_handler')) {
            return;
        }

        $definition = $container->findDefinition('smileeztfa.security.auth_handler');
        $taggedServices = $container->findTaggedServiceIds('smileeztfa.provider');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('addProvider', array(
                    new Reference($id),
                    $attributes['alias']
                ));
            }
        }
    }
}
