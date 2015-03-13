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
     */
    public function getCacheHandlerChain()
    {
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
