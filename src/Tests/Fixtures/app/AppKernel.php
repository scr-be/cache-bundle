<?php
/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Class AppKernel
 *
 * @package Scribe\CacheBundle\Tests
 */
class AppKernel extends Kernel
{
    /**
     * Return array of bundles requires for tests
     *
     * @return array
     */
    public function registerBundles()
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Scribe\CacheBundle\ScribeCacheBundle(),
            new \Scribe\MantleBundle\ScribeMantleBundle()
        ];
    }

    /**
     * Load the required config file based on the environment
     *
     * @param LoaderInterface $loader
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}

/* EOF */
