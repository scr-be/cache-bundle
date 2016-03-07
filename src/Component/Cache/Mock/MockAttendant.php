<?php

/*
 * This file is part of the Teavee Block Manager Bundle.
 *
 * (c) Scribe Inc.     <oss@scr.be>
 * (c) Rob Frawley 2nd <rmf@scr.be>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\Teavee\ObjectCacheBundle\Component\Cache\Mock;

use Scribe\Teavee\ObjectCacheBundle\Component\Cache\AbstractCacheAttendant;

/**
 * Class MockAttendant.
 */
class MockAttendant extends AbstractCacheAttendant
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
        return null;
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

    /**
     * @return string[]
     */
    protected function listCacheKeys()
    {
        return (array) [];
    }
}

/* EOF */
