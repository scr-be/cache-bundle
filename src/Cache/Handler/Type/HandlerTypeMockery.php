<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Cache\Handler\Type;

/**
 * Class HandlerTypeMockery.
 */
class HandlerTypeMockery extends AbstractHandlerType
{
    /**
     * Check if the handler type is supported by the current environment.
     *
     * @return bool
     */
    public function isSupported()
    {
        if (null !== ($decision = $this->callSupportedDecider())) {
            return (bool) $decision;
        }

        return (bool) true;
    }

    /**
     * Overwrite parent implementation of set key for mockery handler type.
     *
     * @param ...mixed $keyValues
     *
     * @return $this
     */
    public function setKey(...$keyValues)
    {
        return $this;
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
     * Overwrite parent implementation of has key for mockery handler type.
     *
     * @return bool
     */
    public function hasKey()
    {
        return true;
    }

    /**
     * Retrieve the cached data using the provided key.
     *
     * @param string $key
     *
     * @return string|null
     */
    protected function getUsingHandler($key)
    {
        return;
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
