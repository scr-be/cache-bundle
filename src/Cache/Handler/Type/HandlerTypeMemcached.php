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
 * Class HandlerTypeMemcached
 *
 * @package Scribe\CacheBundle\Cache\Handler\Type
 */
class HandlerTypeMemcached extends AbstractHandlerType
{
    /**
     * Check if the handler type is supported by the current environment
     *
     * @return bool
     */
    public function isSupported()
    {
        return (bool) (true === extension_loaded('memcached'));
    }

    /**
     * Retrieve the cached data using the provided key
     *
     * @param  string $key
     * @return string|null
     */
    protected function getUsingHandler($key)
    {

    }

    /**
     * Set the cached data using the key (overwriting data that may exist already)
     *
     * @param  string $data
     * @param  string $key
     * @return bool
     */
    protected function setUsingHandler($data, $key)
    {

    }

    /**
     * Check if the cached data exists using the provided key
     *
     * @param  string $key
     * @return bool
     */
    protected function hasUsingHandler($key)
    {

    }

    /**
     * Delete the cached data using the provided key
     *
     * @param  string $key
     * @return bool
     */
    protected function delUsingHandler($key)
    {

    }

    /**
     * Flush all cached data within this cache mechanism-type
     *
     * @return bool
     */
    protected function flushAllUsingHandler()
    {

    }
}

/* EOF */
