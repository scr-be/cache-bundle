<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <oss@scr.be>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Component\Cache\Memcached;

/**
 * Class MemcachedCacheMethodInterface.
 */
interface MemcachedCacheMethodInterface
{
    /**
     * Default server port.
     *
     * @var int
     */
    const DEFAULT_PORT = 11211;

    /**
     * Default server weight.
     *
     * @var int
     */
    const DEFAULT_WEIGHT = 0;
}

/* EOF */
