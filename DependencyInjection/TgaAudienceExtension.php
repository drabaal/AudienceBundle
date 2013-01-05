<?php

namespace Tga\AudienceBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;


class TgaAudienceExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

	    $container->setParameter('tga_audience.session_duration', $config['session_duration']);
	    $container->setParameter('tga_audience.disabled_routes', $config['disabled_routes']);
	    $container->setParameter('tga_audience.environnements', $config['environnements']);
    }
}
