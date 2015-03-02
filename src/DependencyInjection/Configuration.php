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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;


/**
 * Class Configuration
 *
 * @package Scribe\CacheBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Create the config tree builder object
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('scribe_cache');

        $rootNode
            ->children()
                ->append($this->getGlobalNode())
                ->append($this->getMethodsNode())
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * Create global cache config
     *
     * @return NodeDefinition
     */
    private function getGlobalNode()
    {
        return (new TreeBuilder)
            ->root('global')
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enabled')
                    ->defaultTrue()
                    ->info('If this bundle is loaded in the kernel it is enabled by default for whatever mechanisms choose to use it.')
                ->end()
            ->end()
        ;
    }

    /**
     * Create cache method selection mode
     *
     * @return NodeDefinition
     */
    private function getMethodsNode()
    {
        return (new TreeBuilder)
            ->root('methods')
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('priority')
                    ->defaultValue([])
                    ->info('If empty, attempt to use all available cache methods using their default priority.')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;
    }
}

/* EOF */
