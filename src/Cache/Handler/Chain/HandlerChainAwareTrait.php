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

use Scribe\CacheBundle\Exceptions\RuntimeException;

/**
 * Trait HandlerChainAwareTrait.
 */
trait HandlerChainAwareTrait
{
    /**
     * An instance of a class implementing KeyGeneratorInterface.
     *
     * @var AbstractHandlerChain|null
     */
    private $cacheHandlerChain = null;

    /**
     * Set the cache handler chain.
     *
     * @param AbstractHandlerChain|null $cacheHandlerChain
     *
     * @return $this
     */
    public function setCacheHandlerChain(AbstractHandlerChain $cacheHandlerChain = null)
    {
        $this->cacheHandlerChain = $cacheHandlerChain;

        return $this;
    }

    /**
     * Get the key generator instance.
     *
     * @return AbstractHandlerChain|null
     *
     * @throws RuntimeException When a cache handler chain has not been set.
     */
    public function getCacheHandlerChain()
    {
        if (false === $this->hasCacheHandlerChain()) {
            throw new RuntimeException(sprintf(
                'You requested a cache chain handler via the method %s declared in trait %s and used in %s, but no handler chain has been set.',
                __FUNCTION__,
                'Scribe\CacheBundle\Cache\Handler\Chain\HandlerChainAwareTrait',
                get_class()
            ));
        }

        return $this->cacheHandlerChain;
    }

    /**
     * Check if the cache handler chain exists.
     *
     * @return bool
     */
    public function hasCacheHandlerChain()
    {
        return (bool) ($this->cacheHandlerChain instanceof AbstractHandlerChain);
    }
}

/* EOF */
