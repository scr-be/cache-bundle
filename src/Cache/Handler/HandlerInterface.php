<?php
/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Cache\Handler;

/**
 * Interface HandlerInterface
 *
 * @package Scribe\CacheBundle\Cache\Handler
 */
interface HandlerInterface
{
    public function setEnabled($cacheEnabled = true);
    public function isEnabled();

    /**
     * Set the value(s) that create the cache key
     *
     * @param  ...mixed $keyValues
     * @return $this
     */
    public function setKey(...$keyValues);

    /**
     * Get the compiled key string
     *
     * @return string
     */
    public function getKey();

    /**
     * Check if a key has been setup
     *
     * @return bool
     */
    public function hasKey();

    /**
     * Attempt to get a cached value; returns null if value does not exist or
     * is stale.
     *
     * @param  ...mixed $keyValues
     * @return string|int|object|callable|null
     */
    public function get(...$keyValues);

    /**
     * Set a cached value; will overwrite a value with the same key silently
     *
     * @param  string|int|object|callable $data
     * @param  ...mixed                   $keyValues
     * @return bool
     */
    public function set($data, ...$keyValues);

    /**
     * Check for non-stale existence of cached value with same key
     *
     * @param  ...mixed $keyValues
     * @return bool
     */
    public function has(...$keyValues);

    /**
     * Delete a cache value
     *
     * @param  ...mixed $keyValues
     * @return bool
     */
    public function del(...$keyValues);

    /**
     * Flush all cached values (this could potentially be more than values stored
     * using only this API, but also, for instance, Doctrine's APCu cache if using
     * the APUc handler).
     *
     * @return bool
     */
    public function flushAll();
}

/* EOF */
