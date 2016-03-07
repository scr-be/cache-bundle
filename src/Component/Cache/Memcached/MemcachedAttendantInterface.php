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

namespace Scribe\Teavee\ObjectCacheBundle\Component\Cache\Memcached;

/**
 * Class MemcachedAttendantInterface.
 */
interface MemcachedAttendantInterface
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
