<?php

/*
 * This file is part of the Teavee Block Manager Bundle.
 *
 * (c) Scribe Inc.     <oss@scr.be>
 * (c) Rob Frawley 2nd <rmf@scr.be>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\Teavee\ObjectCacheBundle\DependencyInjection;

use Scribe\WonkaBundle\Component\DependencyInjection\AbstractConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

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
                ->append($this->getMethodRedisNode())
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
                        ->enumNode('hash')
                            ->values(['hash_default', 'hash_md5', 'hash_crc', 'hash_fnv1_64', 'hash_fnv1a_64', 'hash_hsieh', 'hash_murmur'])
                            ->defaultValue('hash_default')
                            ->info('Hash algorithm used internally.')
                        ->end()
                        ->enumNode('serializer')
                            ->values(['serializer_igbinary', 'serializer_php', 'serializer_json', 'serializer_json_array'])
                            ->defaultValue('serializer_php')
                            ->info('Set the serializer.')
                        ->end()
                        ->enumNode('distribution')
                            ->values(['distribution_modula', 'distribution_consistent'])
                            ->defaultValue('distribution_modula')
                        ->end()
                        ->booleanNode('libketama_compatible')
                            ->defaultFalse()
                            ->info('Toggle libketama-compatible behavior.')
                        ->end()
                        ->booleanNode('no_block')
                            ->defaultFalse()
                            ->info('Toggle asynchronous I/O.')
                        ->end()
                        ->booleanNode('noreply')
                            ->defaultFalse()
                            ->info('Enable no-reply mode.')
                        ->end()
                        ->booleanNode('buffer_writes')
                            ->defaultFalse()
                            ->info('Should writes be buffered?')
                        ->end()
                        ->booleanNode('tcp_nodelay')
                            ->defaultFalse()
                            ->info('Toggle TCP no-delay.')
                        ->end()
                        ->booleanNode('tcp_keepalive')
                            ->defaultTrue()
                            ->info('Toggle TCP keepalive.')
                        ->end()
                        ->booleanNode('binary_protocol')
                            ->defaultFalse()
                            ->info('Toggle protocol method to binary.')
                        ->end()
                        ->booleanNode('compression')
                            ->defaultTrue()
                            ->info('Toggle compression.')
                        ->end()
                        ->enumNode('compression_type')
                            ->values(['compression_zlib', 'compression_fastlz'])
                            ->defaultValue('compression_fastlz')
                            ->info('Set the compression method.')
                        ->end()
                        ->integerNode('connect_timeout')
                            ->defaultValue(1000)
                            ->min(1)
                            ->info('Socket connection timeout in non-blocking mode (milliseconds).')
                        ->end()
                        ->integerNode('retry_timeout')
                            ->defaultValue(0)
                            ->min(0)
                            ->info('Wait before retrying failed connection attempt (seconds).')
                        ->end()
                        ->integerNode('send_timeout')
                            ->defaultValue(0)
                            ->min(0)
                            ->info('Send timeout (seconds).')
                        ->end()
                        ->integerNode('recv_timeout')
                            ->defaultValue(0)
                            ->min(0)
                            ->info('Receive timeout (seconds).')
                        ->end()
                        ->integerNode('poll_timeout')
                            ->defaultValue(1000)
                            ->min(1)
                            ->info('Connection polling timeout (milliseconds).')
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
    private function getMethodRedisNode()
    {
        return $this
            ->getBuilder('redis')
            ->getNodeDefinition()
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->getAttendantGeneralNode(1, false))
                ->arrayNode('options_list')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('serializer')
                            ->values(['serializer_igbinary', 'serializer_php', 'serializer_none'])
                            ->defaultValue('serializer_igbinary')
                            ->info('Set the serializer.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('configs_list')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('host')
                            ->isRequired()
                            ->defaultValue('127.0.0.1')
                            ->info('The hostname or ip address the redis server.')
                        ->end()
                        ->integerNode('port')
                            ->defaultValue(6379)
                            ->min(1)
                            ->info('The port of a redis server.')
                        ->end()
                        ->integerNode('timeout')
                            ->defaultValue(2)
                            ->info('The client/server communication timeout.')
                        ->end()
                        ->scalarNode('reserved')
                            ->defaultValue(null)
                            ->info('Reserved value for connection.')
                        ->end()
                        ->integerNode('retry_interval')
                            ->defaultValue(100)
                            ->info('The client/server communication retry interval (in milliseconds)')
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
                    ->integerNode('priority')
                        ->defaultValue((int) $priority)
                        ->min(0)
                        ->info('Set the priority for this cache method.')
                    ->end()
                    ->booleanNode('enabled')
                        ->defaultValue($isMock === true ? true : false)
                        ->info('Toggle method on or off.')
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
