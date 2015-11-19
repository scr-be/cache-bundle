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

use Scribe\CacheBundle\Component\Manager\CacheManagerInterface;
use Scribe\CacheBundle\Component\Cache\CacheMethodInterface;

/**
 * Trait CacheManagerAwareTrait.
 */
trait CacheManagerAwareTrait
{
    /**
     * @var CacheManagerInterface
     */
    protected $cacheManager;

    /**
     * Set the key generator instance.
     *
     * @param CacheManagerInterface $cacheManager
     *
     * @return $this
     */
    public function setCacheManager(CacheManagerInterface $cacheManager)
    {
        $this->cacheManager = $cacheManager;

        return $this;
    }

    /**
     * Get the key generator instance.
     *
     * @return CacheManagerInterface
     */
    public function getCacheManager()
    {
        return $this->cacheManager;
    }

    /**
     * @return CacheMethodInterface
     */
    public function getCache()
    {
        return $this
            ->cacheManager
            ->getActive();
    }
}

/* EOF */
