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
use Scribe\Component\DependencyInjection\Aware\EntityManagerAwareTrait;
use Scribe\Doctrine\Exception\ORMException;
use Scribe\Utility\Extension;

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
     * @var bool
     */
    protected $initialized;

    /**
     * @return boolean
     */
    public function isInitialized()
    {
        return $this->initialized;
    }

    /**
     * @param boolean $initialized
     *
     * @return $this
     */
    public function setInitialized($initialized)
    {
        $this->initialized = $initialized;

        return $this;
    }

    /**
     * @return $this
     */
    protected function setUninitializedAndDisabledHard()
    {
        $this->setInitialized(false);
        $this->setSupportedDecider(function() { return false; });

        return $this;
    }

    /**
     * Set the required repositories for cache handler.
     *
     * @param $em         EntityManager
     * @param $itemRepo   CacheDBHandlerItemRepository
     * @param $prefixRepo CacheDBHandlerPrefixRepository
     *
     * @return $this
     */
    public function initManagerAndRepositories(EntityManager $em, CacheDBHandlerItemRepository $itemRepo, CacheDBHandlerPrefixRepository $prefixRepo)
    {
        if (true !== $this->handleDetermineInitState()) {
            return $this;
        }

        $this->setEntityManager($em);
        $this->setRepositories($itemRepo, $prefixRepo);
        $this->determinePrefix();
        $this->dispatchCleanup();
        $this->setInitialized(true);

        return $this;
    }

    /**
     * @param CacheDBHandlerItemRepository   $itemRepo
     * @param CacheDBHandlerPrefixRepository $prefixRepo
     *
     * @return $this
     */
    protected function setRepositories(CacheDBHandlerItemRepository $itemRepo, CacheDBHandlerPrefixRepository $prefixRepo)
    {
        $this->itemRepo = $itemRepo;
        $this->prefixRepo = $prefixRepo;

        return $this;
    }

    /**
     * @return bool|$this
     */
    protected function handleDetermineInitState()
    {
        if (false === $this->isSupported()) {
            return $this->setUninitializedAndDisabledHard();
        }

        if (false === $this->isEnabled()) {
            return $this->setUninitializedAndDisabledHard();
        }

        return true;
    }

    /**
     * @return $this
     */
    protected function dispatchCleanup()
    {
        if (mt_rand(0, 100) === 10) {
            $this->flushStaleItems();
        }

        return $this;
    }

    /**
     * Perform any pre-init repository initialization.
     *
     * @param bool $forceLookup
     *
     * @return $this
     */
    protected function determinePrefix($forceLookup = false)
    {
        if ($this->prefix instanceof CacheDBHandlerPrefix &&
            true !== $forceLookup) {
            return $this;
        }

        $prefixSlug = $this->getKeyGenerator()->getKeyPrefix();

        try {
            $this->prefix = $this->prefixRepo->findOneBySlug($prefixSlug);
        } catch (ORMException $e) {
            $this->prefix = $this->initNewPrefix($prefixSlug);
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
        if (false === $this->isInitialized()) {
            return false;
        }

        if ($this->itemRepo->findStaleCountByPrefix($this->prefix) > 0) {
            $this->itemRepo->deleteStaleByPrefix($this->prefix);
        }

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
     * @param mixed ...$by
     *
     * @return bool
     */
    public function isSupported(...$by)
    {
        if (null !== ($decision = $this->callSupportedDecider())) {
            return (bool) $decision;
        }

        return (bool) $this->isSupportedDefaultDecider();
    }

    /**
     * @return bool
     */
    protected function isSupportedDefaultDecider()
    {
        if (false === $this->isEnabled() || false === $this->isInitialized()) {
            return false;
        }

        $hasValidOrmExtension = Extension::areAnyEnabled(
            'mysql', 'mysqli', 'pdo_mysql', 'pgsql', 'pdo_pgsql', 'mongo'
        );

        return (bool) ($hasValidOrmExtension !== false ?: false);
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
        if (false === $this->isInitialized()) {
            return false;
        }

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
        if (false === $this->isInitialized()) {
            return;
        }

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
        if (false === $this->isInitialized()) {
            return false;
        }

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
        if (false === $this->isInitialized()) {
            return false;
        }

        return (bool) ($this->itemRepo->deleteAllByPrefix($this->prefix) > 0 ?: false);
    }
}

/* EOF */
