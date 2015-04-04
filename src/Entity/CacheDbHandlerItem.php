<?php

namespace Scribe\CacheBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Scribe\Entity\AbstractEntity;

/**
 * Entity: CacheDbHandlerItem
 *
 * @package Scribe\CacheBundle\Entity
 */
class CacheDbHandlerItem extends AbstractEntity
{
    /**
     * @var \DateTime
     */
    private $createdOn;

    /**
     * @var \DateTime
     */
    private $updatedOn;

    /**
     * @var \DateTime
     */
    private $deletedOn;

    /**
     * @var CacheDbHandlerPrefix|null
     */
    private $prefix;

    /**
     * @var string
     */
    private $cacheKey;

    /**
     * @var string
     */
    private $cacheValue;

    /**
     * Support casting to string
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->prefix;
    }

    /**
     * Set prefix association
     *
     * @param CacheDbHandlerPrefix|null $prefix
     *
     * @return $this
     */
    public function setPrefix(CacheDbHandlerPrefix $prefix = null)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get prefix association
     *
     * @return CacheDbHandlerPrefix|null
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
    public function setCacheKey($cacheKey)
    {
        $this->cacheKey = $cacheKey;

        return $this;
    }

    /**
     * Get cacheKey
     *
     * @return string 
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }

    /**
     * Set cacheValue
     *
     * @param string $cacheValue
     * @return $this
     */
    public function setCacheValue($cacheValue)
    {
        $this->cacheValue = $cacheValue;

        return $this;
    }

    /**
     * Get cacheValue
     *
     * @return string 
     */
    public function getCacheValue()
    {
        return $this->cacheValue;
    }
}

/* EOF */
