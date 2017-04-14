<?php

namespace Smile\EzTFABundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use EzSystems\PlatformUIBundle\DependencyInjection\PlatformUIExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SmileEzTFAExtension extends Extension implements PlatformUIExtension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('default_settings.yml');
        $loader->load('services.yml');

        $processor = new ConfigurationProcessor($container, 'smile_ez_tfa');
        $processor->mapSetting('providers', $config);
    }

    /**
     * Returns the translation domains used by the extension.
     * @return array An array of extensions
     */
    public function getTranslationDomains()
    {
        return [
            'smileeztfa'
        ];
    }
}
