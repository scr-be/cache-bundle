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
                ->append($this->getHandlersNode())
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * Define the global state config of the cache bundle
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
                    ->info(
                        'To disable all caching operations within this bundle ' .
                        'globally, you can set this value to false.'
                    )
                ->end()
                ->scalarNode('prefix')
                    ->defaultValue('scribe_cache')
                    ->info(
                        'A unique prefix to assign to all cache keys managed by ' .
                        'this bundle. This allows for flushing of cache values ' .
                        'without potentially clearing values managed by other ' .
                        'applications or other bundles.'
                    )
                ->end()
            ->end()
        ;
    }

    /**
     * Define the per-handler configuration options
     *
     * @return NodeDefinition
     */
    private function getHandlersNode()
    {
        return (new TreeBuilder)
            ->root('handlers')
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->getHandlerApcuNode())
                ->append($this->getHandlerMemcachedNode())
                ->append($this->getHandlerFilesystemNode())
            ->end()
        ;
    }

    /**
     * getHandlerTypeGenericInnerNode
     *
     * @return NodeDefinition
     */
    private function getHandlerInnerGenericNode($priority)
    {
        return (new TreeBuilder)
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
                        'When resolving a supported handler type based on the ' .
                        'default cache handler chain, define the priority this ' .
                        'handler type should have.'
                    )
                ->end()
                ->integerNode('ttl')
                    ->defaultValue(1800)
                    ->min(0)
                    ->info(
                        'The TTL (time to live) for data cached using this handler ' .
                        'type, defined in seconds.'
                    )
                ->end()
            ->end()
        ;
    }

    /**
     * Define the apcu handler configuration options
     *
     * @return NodeDefinition
     */
    private function getHandlerApcuNode()
    {
        return (new TreeBuilder)
            ->root('apcu')
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->getHandlerInnerGenericNode(1))
            ->end()
        ;
    }

    /**
     * Define the memcached handler configuration options
     *
     * @return NodeDefinition
     */
    private function getHandlerMemcachedNode()
    {
        return (new TreeBuilder)
            ->root('memcached')
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->getHandlerInnerGenericNode(2))
                ->arrayNode('compression')
                    ->children()
                        ->enumNode('type')
                            ->values(['fastlz', 'zlib'])
                            ->defaultValue('fastlz')
                            ->info('Set the compression type.')
                        ->end()
                        ->floatNode('factor')
                            ->defaultValue(1.3)
                            ->min(0)->max(5)
                            ->info(
                                'Set the compression factor. Used to determine ' .
                                'weather to store the compressed value or not ' .
                                'only if the compression factor (saving) exceeds ' .
                                'the set limit. Store compressed if: plain_len > ' .
                                'comp_len * factor. The default value is 1.3 ' .
                                '(23% space saving).'
                            )
                        ->end()
                        ->integerNode('threshold')
                            ->defaultValue(2000)
                            ->min(0)
                            ->info(
                                'The compression threshold in bytes. Data below ' .
                                'this value will not be considered for compression.'
                            )
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('internals')
                    ->children()
                        ->enumNode('serializer')
                            ->values(['igbinary', 'php', 'json_array', 'json'])
                            ->defaultValue('igbinary')
                            ->info(
                                'Set the default serializer for memcache objected. ' .
                                'Note that while the "json" and "json_array" ' .
                                'serializer is fast and compact, it only works on ' .
                                'UTF-8 data and does not fully implement serializing. ' .
                                'Do note that the default value of "igbinary" will ' .
                                'automatically fallback to "php" in the event that ' .
                                'the igbinary php module is not loaded or memcached ' .
                                'was not compiled with igbinary support.'
                            )
                        ->end()
                        ->booleanNode('libketama_compatible')
                            ->defaultFalse()
                            ->info(
                                'Enables or disables compatibility with libketama-like ' .
                                'behavior. Recommended when other libketama-based clients ' .
                                '(Python, Ruby, etc.) will be utalizing the same keys.'
                            )
                        ->end()
                        ->booleanNode('io_no_block')
                            ->defaultFalse()
                            ->info(
                                'Enables or disables asynchronous I/O. This is the ' .
                                'fastest transport available for storage functions.'
                            )
                        ->end()
                        ->booleanNode('tcp_no_delay')
                            ->defaultFalse()
                            ->info(
                                'Enables or disables the no-delay feature for ' .
                                'connecting sockets (may be faster in some ' .
                                'environments).'
                            )
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('!a::servers')
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
                                    'The weight of the server relative to the total ' .
                                    'weight of all the servers in the pool. This ' .
                                    'controls the probability of the server being ' .
                                    'selected for operations.'
                                )
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Define the filesystem handler configuration options
     *
     * @return NodeDefinition
     */
    private function getHandlerFilesystemNode()
    {
        return (new TreeBuilder)
            ->root('filesystem')
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->getHandlerInnerGenericNode(3))
                ->scalarNode('cache_dir')
                    ->isRequired()
                    ->defaultValue('/tmp')
                    ->info('The directory to use for filesystem caching.')
                ->end()
            ->end()
        ;
    }
}

/* EOF */
