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

use Scribe\CacheBundle\Cache\Handler\Engine\AbstractCacheEngine;
use Scribe\Component\DependencyInjection\Compiler\CompilerPassChainInterface;
use Scribe\Component\DependencyInjection\Compiler\CompilerPassHandlerInterface;
use Scribe\CacheBundle\Exceptions\RuntimeException;

/**
 * Class CacheChainInterface.
 */
interface CacheChainInterface extends CompilerPassChainInterface
{
    /**
     * Re-determine active handler, possibly based on forced selection.
     *
     * @param string|null $forceType
     *
     * @return AbstractCacheChain
     */
    public function reDetermineActiveHandler($forceType = null);

    /**
     * Sets the active handler.
     *
     * @param CompilerPassHandlerInterface $handler
     *
     * @return $this
     */
    public function setActiveHandler(CompilerPassHandlerInterface $handler);

    /**
     * Gets the active handler.
     *
     * @return AbstractCacheEngine
     *
     * @throws RuntimeException
     */
    public function getActiveHandler();

    /**
     * Checks if an active handler has been set.
     *
     * @return bool
     */
    public function hasActiveHandler();

    /**
     * Get the active handler type, by default the short name of class such as
     * simply "apcu" but optionally return the fully-qualified class name.
     *
     * @param bool $fullyQualified
     *
     * @return string
     */
    public function getActiveHandlerType($fullyQualified = false);

    /**
     * Quite literally un-sets the chosen active handler. Calling {@see getActiveHandler()} without
     * re-determining the active handler will provide you with a mocked cache implementation.
     *
     * @return $this
     */
    public function clearActiveHandlerType();

    /**
     * Set the value(s) that create the cache key.
     *
     * @param ...mixed $keyValues
     *
     * @return $this
     */
    public function setKey(...$keyValues);

    /**
     * Get the compiled key string.
     *
     * @return string|null
     */
    public function getKey();

    /**
     * Check if a key has been setup.
     *
     * @return bool
     */
    public function hasKey();

    /**
     * Attempt to get a cached value; returns null if value does not exist or
     * is stale.
     *
     * @param ...mixed $keyValues
     *
     * @return string|int|object|callable|null
     */
    public function get(...$keyValues);

    /**
     * Set a cached value; will overwrite a value with the same key silently.
     *
     * @param string|int|object|callable $data
     * @param ...mixed                   $keyValues
     *
     * @return bool
     */
    public function set($data, ...$keyValues);

    /**
     * Check for non-stale existence of cached value with same key.
     *
     * @param ...$keyValues
     *
     * @return bool
     */
    public function has(...$keyValues);

    /**
     * Delete the cached data using the provided key.
     *
     * @param ...mixed $keyValues
     *
     * @return bool
     */
    public function del(...$keyValues);

    /**
     * Flush all cached data within this cache mechanism-type.
     *
     * @return bool
     */
    public function flushAll();

    /**
     * Set the time to live for the cache values.
     *
     * @param int $seconds
     *
     * @return $this
     */
    public function setTtl($seconds);

    /**
     * Get the TTL for the cache values.
     *
     * @return int
     */
    public function getTtl();

    /**
     * Set the TTL back to the system default.
     *
     * @return $this
     */
    public function setTtlToDefault();
}

/* EOF */
