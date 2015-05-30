<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Cache\Handler\Engine;

/**
 * Class CacheEngineMock.
 */
class CacheEngineMock extends AbstractCacheEngine
{
    /**
     * Check if the handler type is supported by the current environment.
     *
     * @return bool
     */
    public function isSupportedDefaultDecider(...$by)
    {
        return (bool) true;
    }

    /**
     * Overwrite parent implementation of get key for mockery handler type.
     *
     * @return string|null
     */
    public function getKey()
    {
        return;
    }

    /**
     * Overwrite parent implementation of set key for mockery handler type.
     *
     * @param mixed,... $keyValues
     *
     * @return $this;
     */
    public function setKey(...$keyValues)
    {
        return $this;
    }

    /**
     * Overwrite parent implementation of has key for mockery handler type.
     *
     * @return bool
     */
    public function hasKey()
    {
        return true;
    }

    /**
     * Overwrite parent implementation of get for mockery handler type.
     *
     * @param mixed,... $keyValues
     *
     * @return null
     */
    public function get(...$keyValues)
    {
        return $this->getUsingHandler($keyValues);
    }

    /**
     * Overwrite parent implementation of set for mockery handler type.
     *
     * @param string|int|object|callable  $data
     * @param mixed,...                   $keyValues
     *
     * @return bool
     */
    public function set($data, ...$keyValues)
    {
        return $this->setUsingHandler($data, $keyValues);
    }

    /**
     * Overwrite parent implementation of has for mockery handler type.
     *
     * @param string,... $key
     *
     * @return bool
     */
    public function has(...$key)
    {
        return $this->hasUsingHandler($key);
    }

    /**
     * Overwrite parent implementation of del for mockery handler type.
     *
     * @param string,... $keyValues
     *
     * @return bool
     */
    public function del(...$keyValues)
    {
        return $this->delUsingHandler($keyValues);
    }

    /**
     * Overwrite parent implementation of flushAll for mockery handler type.
     *
     * @return bool
     */
    public function flushAll()
    {
        return $this->flushAllUsingHandler();
    }

    /**
     * Retrieve the cached data using the provided key.
     *
     * @param string $key
     *
     * @return null
     */
    protected function getUsingHandler($key)
    {
        return null;
    }

    /**
     * Set the cached data using the key (overwriting data that may exist already).
     *
     * @param string $data
     * @param string $key
     *
     * @return bool
     */
    protected function setUsingHandler($data, $key)
    {
        return false;
    }

    /**
     * Check if the cached data exists using the provided key.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function hasUsingHandler($key)
    {
        return false;
    }

    /**
     * Delete the cached data using the provided key.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function delUsingHandler($key)
    {
        return false;
    }

    /**
     * Flush all cached data within this cache mechanism-type.
     *
     * @return bool
     */
    protected function flushAllUsingHandler()
    {
        return false;
    }
}

/* EOF */
