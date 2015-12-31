<?php

/*
 * This file is part of the Teavee Object Caching Bundle.
 *
 * (c) Scribe Inc.     <oss@scr.be>
 * (c) Rob Frawley 2nd <rmf@scr.be>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\Teavee\ObjectCacheBundle\DependencyInjection;

use Scribe\WonkaBundle\Component\DependencyInjection\AbstractConfiguration;

/**
 * Class Configuration.
 */
class Configuration extends AbstractConfiguration
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $this
            ->getBuilderRoot()
            ->getNodeDefinition()
            ->canBeEnabled()
            ->children()
                ->append($this->getGeneratorNode())
                ->append($this->getMethodNode())
            ->end();

        return $this
            ->getBuilderRoot()
            ->getTreeBuilder();
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function getGeneratorNode()
    {
        return $this
            ->getBuilder('generator')
            ->getNodeDefinition()
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('prefix')
                    ->defaultValue('dflt-key-prefix')
                    ->info('String used to avoid collision of cache values by prepending a unique prefix to keys created.')
                ->end()
                ->scalarNode('algorithm')
                    ->defaultValue('md5')
                    ->info('Algorithm used to hash keys. Available values can be found via hash_algos() output.')
                ->end()
            ->end();
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function getMethodNode()
    {
        return $this
            ->getBuilder('attendant')
            ->getNodeDefinition()
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->getMethodMemcachedNode())
                ->append($this->getMethodMockNode())
            ->end();
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function getMethodMemcachedNode()
    {
        return $this
            ->getBuilder('memcached')
            ->getNodeDefinition()
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->getAttendantGeneralNode(0, false))
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

    /**
     * @return ArrayNodeDefinition
     */
    private function getMethodMockNode()
    {
        return $this
            ->getBuilder('mock')
            ->getNodeDefinition()
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->getAttendantGeneralNode(9, true))
            ->end();
    }

    /**
     * @param int  $priority
     * @param bool $isMock
     *#
     *
     * @return ArrayNodeDefinition
     */
    private function getAttendantGeneralNode($priority, $isMock = false)
    {
        $b = $this
            ->getGhostBuilder('general')
            ->getNodeDefinition()
            ->addDefaultsIfNotSet()
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')
                    ->defaultValue((bool) !$isMock)
                    ->info('Toggle this cache method on or off.')
                ->end()
                ->integerNode('priority')
                    ->defaultValue((int) $priority)
                    ->min(0)
                    ->info('Set the priority for this cache method.')
                ->end();

        if ($isMock !== true) {
            return $b
                ->integerNode('ttl')
                    ->defaultValue(3600)
                    ->min(1)
                    ->info('Set default TTL for cache data (in seconds).')
                ->end()
            ->end();
        }

        return $b->end();
    }
}

/* EOF */
