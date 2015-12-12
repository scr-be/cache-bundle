<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\DependencyInjection\Aware;

use Scribe\CacheBundle\Component\Manager\CacheManagerInterface;
use Scribe\CacheBundle\Component\Cache\CacheMethodInterface;
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
     * @return CacheMethodInterface
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
