<?php

namespace Smile\EzTFABundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration extends SiteAccessConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('smile_ez_tfa');

        $systemNode = $this->generateScopeBaseNode($rootNode);
        $systemNode
            ->arrayNode('providers')
                ->prototype('array')
                    ->prototype('scalar')->end()
                ->end()
            ->end();
        return $treeBuilder;
    }
}
