<?php
/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Scribe\CacheBundle\Exceptions\RuntimeException;

/**
 * Class ScribeCacheExtension
 *
 * @package Scribe\CacheBundle\DependencyInjection
 */
class ScribeCacheExtension extends Extension
{
    /**
     * Load the configuration directives/files for this bundle
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     * @throws RuntimeException
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('s.cache.enabled', $config[ 'enabled' ]);
        $container->setParameter('s.cache.service', $config[ 'service' ]);

        if (true === in_array('enabled', $config) && $config[ 'enabled' ] == true) {
            $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
            $loader->load('services.yml');

            if (false === $container->has($config[ 'service' ])) {
                throw new RuntimeException(
                    sprintf("An invalid cache service %s has been configured.", $config[ 'service' ])
                );
            }
        }
    }
}

/* EOF */