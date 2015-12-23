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

namespace Scribe\CacheBundle\DependencyInjection\Aware;

use Scribe\Teavee\ObjectCacheBundle\DependencyInjection\Aware\CacheManagerAwareTrait as MovedCacheManagerAwareTrait;

/**
 * @deprecated v0.3 Moved {@see cribe\Teavee\ObjectCachingBundle\DependencyInjection\Aware\CacheManagerAwareTrait}
 * 
 * Trait CacheManagerAwareTrait.
 */
trait CacheManagerAwareTrait
{
    use MovedCacheManagerAwareTrait;
}

/* EOF */
