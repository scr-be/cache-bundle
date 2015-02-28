<?php
/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\UserlandHandler;

use Scribe\CacheBundle\UserlandHandler\UserlandCacheInterface;

/**
 * Trait UserlandCacheAwareTrait
 *
 * @package Scribe\CacheBundle\UserlandHandler
 */
trait UserlandCacheAwareTrait
{
    /**
     * @var null|UserlandCacheInterface
     */
    protected $userlandCache;

    /**
     * Setter for userland cache instance
     *
     * @param UserlandCacheInterface $userlandCache
     * @return $this
     */
    protected function setUserlandCache(UserlandCacheInterface $userlandCache)
    {
        $this->userlandCache = $userlandCache;

        return $this;
    }

    /**
     * Getter for userland cache instance
     *
     * @return null|UserlandCacheInterface
     */
    protected function getUserlandCache()
    {
        return $this->userlandCache;
    }

    /**
     * Checker for userland cache instance
     *
     * @return bool
     */
    protected function hasUserlandCache()
    {
        return (bool) ($this->userlandCache instanceof UserlandCacheInterface ? true : false);
    }
}

/* EOF */