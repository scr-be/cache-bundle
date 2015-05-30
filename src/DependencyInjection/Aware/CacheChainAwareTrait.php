<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\DependencyInjection\Aware;

use Scribe\CacheBundle\Cache\Handler\Chain\CacheChainInterface;
use Scribe\CacheBundle\Cache\Handler\Chain\CacheChainMock;

/**
 * Trait CacheChainAwareTrait.
 */
trait CacheChainAwareTrait
{
    /**
     * @var CacheChainInterface
     */
    protected $cacheChain;

    /**
     * Inject the cache chain instance into the object using this trait.
     *
     * @param CacheChainInterface $cacheChain
     *
     * @return $this
     */
    public function setCacheChain(CacheChainInterface $cacheChain)
    {
        $this->cacheChain = $cacheChain;

        return $this;
    }

    /**
     * Returns the provided cache chain
     *
     * @return CacheChainInterface
     */
    public function getCacheChain()
    {
        if ($this->hasCacheChain()) {
            return $this->cacheChain;
        }

        return $this->getCacheChainMock();
    }

    /**
     * Check if the cache handler chain exists.
     *
     * @return bool
     */
    public function hasCacheChain()
    {
        return (bool) ($this->cacheChain instanceof CacheChainInterface);
    }

    /**
     * Return mock cache chain instance.
     *
     * @return CacheChainInterface
     */
    protected function getCacheChainMock()
    {
        return new CacheChainMock();
    }
}

/* EOF */
