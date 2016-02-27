<?php

/*
 * This file is part of the Teavee Object Caching Bundle.
 *
 * (c) Scribe Inc.     <oss@scr.be>
 * (c) Rob Frawley 2nd <rmf@scr.be>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\Teavee\ObjectCacheBundle\Component\Cache\Redis;

/**
 * Class RedisAttendantInterface.
 */
interface RedisAttendantInterface
{
    /**
     * Default server host.
     *
     * @var string
     */
    const DEFAULT_HOST = '127.0.0.1';

    /**
     * Default server port.
     *
     * @var int
     */
    const DEFAULT_PORT = 6379;

    /**
     * Default server timeout.
     *
     * @var int
     */
    const DEFAULT_TIMEOUT = 2;

    /**
     * Default server reserved value.
     *
     * @var mixed
     */
    const DEFAULT_RESERVED = null;

    /**
     * Default server retry (in ms).
     *
     * @var int
     */
    const DEFAULT_RETRY_INTERVAL = 100;
}

/* EOF */
