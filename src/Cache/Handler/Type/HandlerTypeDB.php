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

use Doctrine\ORM\EntityManager;
use Scribe\CacheBundle\Doctrine\Entity\Cache\CacheDBHandlerItem;
use Scribe\CacheBundle\Doctrine\Entity\Cache\CacheDBHandlerPrefix;
use Scribe\CacheBundle\Doctrine\Repository\Cache\CacheDBHandlerItemRepository;
use Scribe\CacheBundle\Doctrine\Repository\Cache\CacheDBHandlerPrefixRepository;
use Scribe\Component\DependencyInjection\EntityManagerAwareTrait;
use Scribe\Doctrine\Exception\ORMException;

/**
 * Class HandlerTypeDB.
 */
class HandlerTypeDB extends AbstractHandlerType
{
    use EntityManagerAwareTrait;

    /**
     * @var CacheDBHandlerItemRepository
     */
    protected $itemRepo;

    /**
     * @var CacheDBHandlerPrefixRepository
     */
    protected $prefixRepo;

    /**
     * @var CacheDBHandlerPrefix|null
     */
    protected $prefix;

    /**
     * Set the required repositories for cache handler.
     *
     * @param $em         EntityManager
     * @param $itemRepo   CacheDBHandlerItemRepository
     * @param $prefixRepo CacheDBHandlerPrefixRepository
     *
     * @return $this
     */
    public function setRepositories(EntityManager $em, CacheDBHandlerItemRepository $itemRepo, CacheDBHandlerPrefixRepository $prefixRepo)
    {
        $this->setEntityManager($em);

        $this->itemRepo = $itemRepo;
        $this->prefixRepo = $prefixRepo;

        return $this;
    }

    /**
     * Perform any pre-init repository initialization.
     *
     * @param bool $forceStaleFlush
     *
     * @throws ORMException
     *
     * @return $this
     */
    public function initRepositories($forceStaleFlush = false)
    {
        $prefixSlug = $this->getKeyGenerator()->getKeyPrefix();

        try {
            $prefix = $this->prefixRepo->findOneBySlug($prefixSlug);
        } catch (ORMException $e) {
            $prefix = $this->initNewPrefix($prefixSlug);
        }

        $this->prefix = $prefix;

        if (true === $forceStaleFlush || mt_rand(1, 100) === 50) {
            $this->flushStaleItems();
        }

        return $this;
    }

    /**
     * Flush all stale items from DB.
     *
     * @return bool
     */
    public function flushStaleItems()
    {
        $em = $this->getEntityManager();

        $items = $this
            ->itemRepo
            ->findByPrefix($this->prefix)
        ;

        foreach ($items as $i) {
            if ($i->getUpdatedOn() <= (new \DateTime(sprintf('-%d seconds', $i->getTtl())))) {
                $em->remove($i);
            }
        }

        $em->flush();

        return true;
    }

    /**
     * Initialize a new prefix in the DB.
     *
     * @param $prefixSlug
     *
     * @return CacheDBHandlerPrefix
     */
    protected function initNewPrefix($prefixSlug)
    {
        $em = $this->getEntityManager();

        $prefix = new CacheDBHandlerPrefix();
        $prefix->setSlug($prefixSlug);

        $em->persist($prefix);
        $em->flush();

        return $prefix;
    }

    /**
     * Check if the handler type is supported by the current environment.
     *
     * @return bool
     */
    public function isSupported()
    {
        if (null !== ($decision = $this->callSupportedDecider())) {
            return (bool) $decision;
        }

        return (bool) true;
    }

    /**
     * Retrieve the cached data using the provided key.
     *
     * @param string $key
     *
     * @return string|null
     */
    protected function getUsingHandler($key)
    {
        $item = $this->hasDBCacheItem($key);

        return ($item instanceof CacheDBHandlerItem ? $item->getValue() : null);
    }

    /**
     * Set the cached data using the key (overwriting data that may exist already).
     *
     * @param string $data
     * @param string $key
     *
     * @return bool
     */
    protected function setUsingHandler($data, $key)
    {
        $em = $this->getEntityManager();
        $item = $this->hasDBCacheItem($key);

        if (null === $item) {
            $item = new CacheDBHandlerItem();
        }

        $item
            ->setPrefix($this->prefix)
            ->setK($key)
            ->setValue($data)
            ->setTtl($this->getTtl())
        ;

        $em->persist($item);
        $em->flush();

        return true;
    }

    /**
     * Check if the cached data exists using the provided key.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function hasUsingHandler($key)
    {
        return (bool) ($this->hasDBCacheItem($key) instanceof CacheDBHandlerItem ?: false);
    }

    /**
     * @param $key
     *
     * @return null|CacheDBHandlerItem
     */
    protected function hasDBCacheItem($key)
    {
        $em = $this->getEntityManager();
        $item = null;

        try {
            $item = $this->itemRepo->findOneByPrefixAndKey($this->prefix, $key);
        } catch (ORMException $e) {
            return;
        }

        if ($item->getUpdatedOn() <= (new \DateTime(sprintf('-%d seconds', $item->getTtl())))) {
            $em->remove($item);
            $em->flush();

            return;
        }

        return $item;
    }

    /**
     * Delete the cached data using the provided key.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function delUsingHandler($key)
    {
        $em = $this->getEntityManager();

        if (($item = $this->hasDBCacheItem($key)) instanceof CacheDBHandlerItem) {
            $em->remove($item);
            $em->flush();

            return true;
        }

        return false;
    }

    /**
     * Flush all cached data within this cache mechanism-type.
     *
     * @return bool
     */
    protected function flushAllUsingHandler()
    {
        $em = $this->getEntityManager();

        $items = (array) $this
            ->itemRepo
            ->findByPrefix($this->prefix)
        ;

        foreach ($items as $i) {
            $em->remove($i);
        }

        $em->flush();

        return true;
    }
}

/* EOF */
