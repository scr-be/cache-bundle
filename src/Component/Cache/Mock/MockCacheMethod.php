<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Component\Cache\Mock;

use Scribe\CacheBundle\Component\Cache\AbstractCacheMethod;

/**
 * Class MockCacheMethod.
 */
class MockCacheMethod extends AbstractCacheMethod
{
    /**
     * Get cache entry.
     *
     * @param string $key
     *
     * @return null|mixed
     */
    protected function getCacheEntry($key)
    {
        return;
    }

    /**
     * Set cache entry.
     *
     * @param string $key
     * @param mixed  $data
     *
     * @return bool
     */
    protected function setCacheEntry($key, $data)
    {
        return true;
    }

    /**
     * Check for cache entry.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function hasCacheEntry($key)
    {
        return false;
    }

    /**
     * Delete cache entry.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function delCacheEntry($key)
    {
        return true;
    }

    /**
     * Flush all cache entries.
     *
     * @return bool
     */
    protected function flushCacheEntries()
    {
        return true;
    }
}

/* EOF */
