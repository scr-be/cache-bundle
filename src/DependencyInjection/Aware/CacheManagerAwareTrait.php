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

namespace Scribe\Teavee\ObjectCacheBundle\DependencyInjection\Aware;

use Scribe\Teavee\ObjectCacheBundle\Component\Manager\CacheManagerInterface;
use Scribe\Teavee\ObjectCacheBundle\Component\Cache\CacheAttendantInterface;
use Scribe\Wonka\Exception\RuntimeException;

/**
 * Trait CacheManagerAwareTrait.
 */
trait CacheManagerAwareTrait
{
    /**
     * @var CacheManagerInterface|null
     */
    protected $cacheManager;

    /**
     * @param CacheManagerInterface $cacheManager
     *
     * @return $this
     */
    public function setCacheManager(CacheManagerInterface $cacheManager = null)
    {
        $this->cacheManager = $cacheManager;

        return $this;
    }

    /**
     * @return CacheManagerInterface
     */
    public function getCacheManager()
    {
        return $this->cacheManager;
    }

    /**
     * @return CacheAttendantInterface
     */
    public function getCache()
    {
        if ($this->isCacheAvailable() === true) {
            return $this->cacheManager->getActive();
        }

        throw new RuntimeException('Cache manager is not available.');
    }

    /**
     * @return bool
     */
    public function isCacheAvailable()
    {
        if ($this->cacheManager === null) {
            return false;
        }

        return (bool) ($this->getCacheManager()->isEnabled());
    }
}

/* EOF */
