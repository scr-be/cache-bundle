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
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

/**
 * Class Configuration.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Create the config tree builder object.
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
                ->append($this->getEngineNode())
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * Define the global state config of the cache bundle.
     *
     * @return NodeDefinition
     */
    private function getGlobalNode()
    {
        return (new TreeBuilder())
            ->root('global')
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enabled')
                    ->defaultTrue()
                    ->info(
                        'To disable all caching operations within this bundle globally, you can set this value to false.'
                    )
                ->end()
                ->scalarNode('prefix')
                    ->defaultValue('scribe_cache')
                    ->info(
                        'A unique prefix to assign to all cache keys managed by this bundle. This allows for flushing of cache values without potentially clearing values managed by other applications or other bundles.'
                    )
                ->end()
            ->end()
        ;
    }

    /**
     * Define the per-handler configuration options.
     *
     * @return NodeDefinition
     */
    private function getEngineNode()
    {
        return (new TreeBuilder())
            ->root('engine')
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->getEngineMemcachedNode())
                ->append($this->getEngineDatabaseNode())
                ->append($this->getEngineFilesystemNode())
            ->end()
        ;
    }

    /**
     * getHandlerTypeGenericInnerNode.
     *
     * @param int $priority
     *
     * @return NodeDefinition
     */
    private function getEngineInnerGenericNode($priority)
    {
        return (new TreeBuilder())
            ->root('general')
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('disabled')
                    ->defaultFalse()
                    ->info('Allows for disabling this cache handler specifically.')
                ->end()
                ->integerNode('priority')
                    ->defaultValue($priority)
                    ->min(1)->max(99)
                    ->info(
                        'When resolving a supported handler type based on the default cache handler chain, define the priority this handler type should have.'
                    )
                ->end()
                ->integerNode('ttl')
                    ->defaultValue(1800)
                    ->min(0)->max(2592000)
                    ->info(
                        'The TTL (time to live) for data cached using this handler type, defined in seconds.'
                    )
                ->end()
            ->end()
        ;
    }

    /**
     * Define the memcached handler configuration options.
     *
     * @return NodeDefinition
     */
    private function getEngineMemcachedNode()
    {
        return (new TreeBuilder())
            ->root('memcached')
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->getEngineInnerGenericNode(1))
                ->arrayNode('internals_list')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('serializer')
                            ->values(['igbinary', 'php', 'json'])
                            ->defaultValue('igbinary')
                            ->info(
                                'Set the default serializer for memcache objected. Note that while the "json" and "json_array" serializer is fast and compact, it only works on UTF-8 data and does not fully implement serializing. Do note that the default value of "igbinary" will automatically fallback to "php" in the event that the igbinary php module is not loaded or memcached was not compiled with igbinary support.'
                            )
                        ->end()
                        ->booleanNode('libketama_compatible')
                            ->defaultFalse()
                            ->info(
                                'Enables or disables compatibility with libketama-like behavior. Recommended when other libketama-based clients (Python, Ruby, etc.) will be utalizing the same keys.'
                            )
                        ->end()
                        ->booleanNode('io_no_block')
                            ->defaultFalse()
                            ->info(
                                'Enables or disables asynchronous I/O. This is the fastest transport available for storage functions.'
                            )
                        ->end()
                        ->booleanNode('tcp_no_delay')
                            ->defaultFalse()
                            ->info(
                                'Enables or disables the no-delay feature for connecting sockets (may be faster in some environments).'
                            )
                        ->end()
                        ->booleanNode('compression')
                            ->defaultTrue()
                            ->info(
                                'Enable or disable payload compression. When enabled, items longer than a certain threshold will be compressed. For further configuration, you must set proper INI settings for memcached.'
                            )
                        ->end()
                        ->enumNode('compression_method')
                            ->values(['zlib', 'fastlz'])
                            ->defaultValue('fastlz')
                            ->info('Set the compression method used.')
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
                                ->min(0)->max(100)
                                ->info(
                                    'The weight of the server relative to the total weight of all the servers in the pool. This controls the probability of the server being selected for operations.'
                                )
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Define the filesystem handler configuration options.
     *
     * @return NodeDefinition
     */
    private function getEngineFilesystemNode()
    {
        return (new TreeBuilder())
            ->root('filesystem')
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->getEngineInnerGenericNode(3))
                ->scalarNode('cache_dir')
                    ->isRequired()
                    ->defaultValue('/tmp')
                    ->info('The directory to use for filesystem caching.')
                ->end()
            ->end()
        ;
    }

    /**
     * Define the filesystem handler configuration options.
     *
     * @return NodeDefinition
     */
    private function getEngineDatabaseNode()
    {
        return (new TreeBuilder())
            ->root('db')
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->getEngineInnerGenericNode(2))
            ->end()
        ;
    }
}

/* EOF */
