<?php
/*
 * This file is part of the Scribe Mantle Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Scribe\Entity\AbstractEntity;
use Scribe\EntityTrait\HasSlug;

/**
 * Entity: CacheDbHandlerPrefix
 *
 * @package Scribe\CacheBundle\Entity
 */
class CacheDbHandlerPrefix extends AbstractEntity
{
    /**
     * Use global traits for entity properties
     */
    use HasSlug;

    /**
     * Collection of cached items associated with prefix
     *
     * @var null|ArrayCollection
     */
    protected $items;

    /**
     * Construct (init) the entity
     */
    public function __construct()
    {
        parent::__construct();

        $this->items = new ArrayCollection;
    }

    /**
     * Allow casting to string
     */
    public function __toString()
    {
        return sprintf(
            'Entity: %s, Id: %n, Slug: %s',
            (string) get_class($this),
            (int)    $this->getId(),
            (string) $this->getSlug()
        );
    }

    /**
     * Get an array collection of the items associated with this prefix
     *
     * @return ArrayCollection|null
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Search the item associations for the provided item
     *
     * @param CacheDbHandlerItem $item
     *
     * @return bool
     */
    public function hasItem(CacheDbHandlerItem $item)
    {
        if ($this->items->contains($item)) {
            return true;
        }

        return false;
    }
}

/* EOF */
