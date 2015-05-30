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

/**
 * Interface CacheChainAwareInterface.
 */
interface CacheChainAwareInterface
{
    /**
     * Inject the cache chain instance into the object using this trait.
     *
     * @param CacheChainInterface $cacheChain
     *
     * @return $this
     */
    public function setCacheChain(CacheChainInterface $cacheChain);

    /**
     * Returns the provided cache chain
     *
     * @return CacheChainInterface
     */
    public function getCacheChain();

    /**
     * Check if the cache handler chain exists.
     *
     * @return bool
     */
    public function hasCacheChain();
}

/* EOF */
