<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Doctrine\Entity\Cache;

use Scribe\Doctrine\Base\Entity\AbstractEntity;
use Scribe\Doctrine\Base\Model\HasValue;
use Scribe\Doctrine\Behavior\Model\Sluggable\SluggableBehaviorTrait;
use Scribe\Doctrine\Behavior\Model\Timestampable\TimestampableBehaviorTrait;

/**
 * Class CacheDBHandlerItem
 */
class CacheDBHandlerItem extends AbstractEntity
{
    use SluggableBehaviorTrait,
        TimestampableBehaviorTrait,
        HasValue;

    /**
     * @var CacheDBHandlerPrefix|null
     */
    private $prefix;

    /**
     * @var string
     */
    private $k;

    /**
     * @var int
     */
    private $ttl;

    /**
     * Support casting to string
     *
     * @return string
     */
    public function __toString()
    {
        return (string) get_class($this) . ':' . ($this->getId() ?: 'not-em-managed');
    }

    /**
     * This entity must not have auto-generated slugs.
     */
    public function getAutoSlugFields()
    {
        return (array) [
            'k',
        ];
    }

    /**
     * Set prefix association
     *
     * @param CacheDBHandlerPrefix|null $prefix
     *
     * @return $this
     */
    public function setPrefix(CacheDBHandlerPrefix $prefix = null)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get prefix association
     *
     * @return CacheDBHandlerPrefix|null
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set cacheKey
     *
     * @param string $cacheKey
     *
     * @return $this
     */
    public function setK($cacheKey)
    {
        $this->k = $cacheKey;

        return $this;
    }

    /**
     * Get cacheKey
     *
     * @return string 
     */
    public function getK()
    {
        return $this->k;
    }

    /**
     * Clear cacheKey
     *
     * @return $this
     */
    public function clearK()
    {
        $this->k = null;

        return $this;
    }

    /**
     * Set ttl
     *
     * @param string $ttl
     *
     * @return $this
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * Get ttl
     *
     * @return string 
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * Clear ttl
     *
     * @return $this
     */
    public function clearTtl()
    {
        $this->ttl = null;

        return $this;
    }
}

/* EOF */
