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

use Scribe\WonkaBundle\Component\DependencyInjection\AbstractConfiguration;

/**
 * Class Configuration.
 */
class Configuration extends AbstractConfiguration
{
    public function getConfigTreeBuilder()
    {
        $this
            ->getBuilderRoot()
            ->getNodeDefinition()
            ->children()
                ->append($this->getGlobalNode())
                ->append($this->getGeneratorNode())
                ->append($this->getMethodNode())
            ->end();

        return $this
            ->getBuilderRoot()
            ->getTreeBuilder();
    }

    private function getGlobalNode()
    {
        return $this
            ->getBuilder('global')
            ->getNodeDefinition()
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enabled')
                    ->defaultTrue()
                    ->info('Enabled and disabled all caching operations.')
                ->end()
            ->end();
    }

    private function getGeneratorNode()
    {
        return $this
            ->getBuilder('generator')
            ->getNodeDefinition()
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('prefix')
                    ->defaultValue('cache-key-generator')
                    ->info('String used to avoid collision of cache values by prepending a unique prefix to keys created.')
                ->end()
                ->scalarNode('algorithm')
                    ->defaultValue('md5')
                    ->info('Algorithm used to hash keys. Available values can be found via hash_algos() output.')
                ->end()
            ->end();
    }

    private function getMethodNode()
    {
        return $this
            ->getBuilder('method')
            ->getNodeDefinition()
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->getMethodMemcachedNode())
                ->append($this->getMethodMockNode())
            ->end();
    }

    private function getMethodMemcachedNode()
    {
        return $this
            ->getBuilder('memcached')
            ->getNodeDefinition()
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('general')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('Toggle this cache method on or off.')
                        ->end()
                        ->integerNode('priority')
                            ->defaultValue(0)
                            ->min(0)
                            ->info('Set the priority for this cache method.')
                        ->end()
                            ->integerNode('ttl')
                            ->defaultValue(3600)
                            ->min(1)
                            ->info('Set default TTL for cache data.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('options_list')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('serializer')
                            ->values(['serializer_igbinary', 'serializer_php', 'serializer_json'])
                            ->defaultValue('serializer_igbinary')
                            ->info('Set the serializer.')
                        ->end()
                        ->booleanNode('libketama_compatible')
                            ->defaultFalse()
                            ->info('Toggle libketama-compatible behavior.')
                        ->end()
                        ->booleanNode('no_block')
                            ->defaultTrue()
                            ->info('Toggle asynchronous I/O.')
                        ->end()
                        ->booleanNode('tcp_nodelay')
                            ->defaultTrue()
                            ->info('Toggle TCP no-delay.')
                        ->end()
                        ->booleanNode('compression')
                            ->defaultFalse()
                            ->info('Toggle compression.')
                        ->end()
                        ->enumNode('compression_type')
                            ->values(['compression_zlib', 'compression_fastlz'])
                            ->defaultValue('compression_fastlz')
                            ->info('Set the compression method.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('servers_list')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->addDefaultChildrenIfNoneSet('default')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')
                                ->isRequired()
                                ->defaultValue('127.0.0.1')
                                ->info('The hostname or ip address of a memcache server.')
                            ->end()
                            ->integerNode('port')
                                ->isRequired()
                                ->defaultValue(11211)
                                ->min(1)
                                ->info('The port of a memcache server.')
                            ->end()
                            ->integerNode('weight')
                                ->defaultValue(0)
                                ->min(0)
                                ->max(1000)
                                ->info('The weight of the server relative to the total weight of all the servers in the pool.')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function getMethodMockNode()
    {
        return $this
            ->getBuilder('mock')
            ->getNodeDefinition()
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('general')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                            ->info('Toggle this cache method on or off.')
                        ->end()
                            ->integerNode('priority')
                            ->defaultValue(9999)
                            ->min(0)
                            ->info('Set the priority for this cache method.')
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}

/* EOF */
