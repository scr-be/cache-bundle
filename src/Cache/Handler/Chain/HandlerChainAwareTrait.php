<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Cache\Handler\Chain;

use Scribe\CacheBundle\DependencyInjection\Aware\CacheChainAwareTrait;
use Scribe\CacheBundle\Exceptions\RuntimeException;
use Scribe\Utility\Error\DeprecationErrorHandler;

/**
 * Trait HandlerChainAwareTrait.
 *
 * @deprecated {@see CacheChainAwareTrait}
 */
trait HandlerChainAwareTrait
{
    use CacheChainAwareTrait;

    /**
     * Set the cache handler chain.
     *
     * @param AbstractCacheChain|null $cacheHandlerChain
     *
     * @return $this
     */
    public function setCacheHandlerChain(AbstractCacheChain $cacheHandlerChain = null)
    {
        static::triggerDeprecationError(__METHOD__, __LINE__);

        $this->setCacheChain($cacheHandlerChain);

        return $this;
    }

    /**
     * Get the key generator instance.
     *
     * @return AbstractCacheChain|null
     *
     * @throws RuntimeException When a cache handler chain has not been set.
     */
    public function getCacheHandlerChain()
    {
        static::triggerDeprecationError(__METHOD__, __LINE__);

        if (false === $this->hasCacheChain()) {
            throw new RuntimeException(sprintf(
                'You requested a cache chain handler via the method %s declared in trait %s and used in %s, but no handler chain has been set.',
                __FUNCTION__,
                'Scribe\CacheBundle\Cache\Handler\Chain\HandlerChainAwareTrait',
                get_class()
            ));
        }

        return $this->getCacheChain();
    }

    /**
     * Check if the cache handler chain exists.
     *
     * @return bool
     */
    public function hasCacheHandlerChain()
    {
        static::triggerDeprecationError(__METHOD__, __LINE__);

        return (bool) $this->hasCacheChain();
    }

    /**
     * @param string $method
     * @param int    $line
     */
    public static function triggerDeprecationError($method, $line)
    {
        DeprecationErrorHandler::trigger(
            $method,
            $line,
            'This trait (and its coorosponding interface) should not be used in favor of Scribe\\CacheBundle\\DependencyInjection\\Aware\\CacheChainAwareTrait',
            '2015-05-27 08:04:00 -0400',
            '2.0.0'
        );
    }
}

/* EOF */
