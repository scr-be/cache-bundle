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
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Scribe\Component\DependencyInjection\AbstractExtension;

/**
 * Class ScribeCacheExtension
 *
 * @package Scribe\CacheBundle\DependencyInjection
 */
class ScribeCacheExtension extends AbstractExtension
{
    /**
     * Load the configuration directives/files for this bundle
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->autoLoad($configs, $container, new Configuration, 's.cache');
    }
}

/* EOF */
