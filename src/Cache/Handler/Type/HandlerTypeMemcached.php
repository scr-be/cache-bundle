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
        return false;
        //return (bool) extension_loaded('memcached');
    }

    /**
     * Get the cached value.
     *
     * @param  string $key
     * @return string
     */
    protected function getValueViaHandlerImplementation($key)
    {

    }

    /**
     * Set the cached value.
     *
     * @param  string $data
     * @param  string $key
     * @return $this
     */
    protected function setValueViaHandlerImplementation($data, $key)
    {

    }

    /**
     * Check for the cached value.
     *
     * @param  string $key
     * @return bool
     */
    protected function hasValueViaHandlerImplementation($key)
    {

    }
}

/* EOF */
