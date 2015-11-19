<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Component\Cache;

/**
 * Interface CacheMethodInterface.
 */
interface CacheMethodInterface
{
    /**
     * @var string
     */
    const INTERFACE_CACHE_NAME = __CLASS__;

    /**
     * Setup the class instance with the required properties.
     *
     * @param bool $enabled
     * @param int  $ttl
     */
    public function __construct($enabled, $ttl = 0);

    /**
     * Determines if the cache method has been initialized.
     *
     * @return bool
     */
    public function isInitialized();

    /**
     * Set the time to live for the cache values.
     *
     * @param int $seconds
     *
     * @return $this
     */
    public function setTtl($seconds);

    /**
     * Set the TTL back to the system default.
     *
     * @return $this
     */
    public function resetTtl();

    /**
     * Set the enabled/disabled state.
     *
     * @param bool|true $state
     *
     * @return $this
     */
    public function setEnabled($state = true);

    /**
     * Get the enabled/disabled state.
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Get cache value.
     *
     * @param mixed,... $keyValues
     *
     * @return null|mixed
     */
    public function get(...$keyValues);

    /**
     * Set cache value.
     *
     * @param mixed     $data
     * @param mixed,... $keyValues
     *
     * @return bool
     */
    public function set($data, ...$keyValues);

    /**
     * Check for cache value.
     *
     * @param mixed,... $keyValues
     *
     * @return bool
     */
    public function has(...$keyValues);

    /**
     * Delete cache entry.
     *
     * @param mixed,... $keyValues
     *
     * @return bool
     */
    public function del(...$keyValues);

    /**
     * Flush all cache entries.
     *
     * @return bool
     */
    public function flush();

    /**
     * Get the compiled key string.
     *
     * @param mixed,... $keyValues
     *
     * @return string
     */
    public function getKey(...$keyValues);

    /**
     * Set the compiled key string.
     *
     * @param mixed,... $keyValues
     *
     * @return $this
     */
    public function setKey(...$keyValues);
}

/* EOF */
