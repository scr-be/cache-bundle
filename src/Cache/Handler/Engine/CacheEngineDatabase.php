<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Cache\Handler\Engine;

use Doctrine\ORM\EntityManager;
use Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabaseItem;
use Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabasePrefix;
use Scribe\CacheBundle\Doctrine\Repository\Cache\CacheEngineDatabaseItemRepository;
use Scribe\CacheBundle\Doctrine\Repository\Cache\CacheEngineDatabasePrefixRepository;
use Scribe\Component\DependencyInjection\Aware\EntityManagerAwareTrait;
use Scribe\Doctrine\Exception\ORMException;
use Scribe\Utility\Extension;

/**
 * Class CacheEngineDatabase.
 */
class CacheEngineDatabase extends AbstractCacheEngine
{
    use EntityManagerAwareTrait;

    /**
     * Defines the likelihood (1 out of the number defined) that the (expensive)
     * stale cache cleanup routine is called.
     *
     * @var int
     */
    const STALE_CLEANUP_LIKELIHOOD = 50;

    /**
     * @var CacheEngineDatabaseItemRepository
     */
    protected $itemRepo;

    /**
     * @var CacheEngineDatabasePrefixRepository
     */
    protected $prefixRepo;

    /**
     * @var CacheEngineDatabasePrefix|null
     */
    protected $prefix;

    /**
     * Set the manager and cache DB repositories required for this cache handler type.
     *
     * @param $manager    EntityManager
     * @param $itemRepo   CacheEngineDatabaseItemRepository
     * @param $prefixRepo CacheEngineDatabasePrefixRepository
     *
     * @return $this
     */
    public function setManagerAndRepositories(EntityManager $manager, CacheEngineDatabaseItemRepository $itemRepo,
                                              CacheEngineDatabasePrefixRepository $prefixRepo)
    {
        $this->setEntityManager($manager);
        $this->setRepositories($itemRepo, $prefixRepo);
        $this->setInitialized(false);

        return $this;
    }

    /**
     * @param CacheEngineDatabaseItemRepository   $itemRepo
     * @param CacheEngineDatabasePrefixRepository $prefixRepo
     *
     * @return $this
     */
    protected function setRepositories(CacheEngineDatabaseItemRepository $itemRepo, CacheEngineDatabasePrefixRepository $prefixRepo)
    {
        $this->itemRepo = $itemRepo;
        $this->prefixRepo = $prefixRepo;

        return $this;
    }

    /**
     * Utilize the lazy initialization to avoid needless database lookups when this handler is part of the cache chain
     * and not the active handler or utilized.
     *
     * @return bool
     */
    protected function lazyInitialize()
    {
        if ($this->isInitialized() === true) {
            return true;
        }

        if ($this->itemRepo === null || $this->prefixRepo === null || $this->em === null ||
            $this->isSupported() === false || $this->isEnabled() === false)
        {
            return false;
        }

        $this->determinePrefix();
        $this->dispatchCleanup();
        $this->setInitialized(true);

        return true;
    }

    /**
     * Dispatch cleanup routine, which flushes all stale items from the DB. As this is an expensive operation,
     * it will only be called once out of every n calls, where n=${see self::STALE_CLEANUP_LIKELIHOOD}.
     *
     * @return $this
     */
    protected function dispatchCleanup()
    {
        if (mt_rand(1, self::STALE_CLEANUP_LIKELIHOOD) === 25) {
            $this->flushStaleItems();
        }

        return $this;
    }

    /**
     * Perform any prefix initialization required.
     *
     * @return $this
     */
    protected function determinePrefix()
    {
        $prefixSlug = $this->getKeyGenerator()->getKeyPrefix();

        try {
            $this->prefix = $this->prefixRepo->findOneBySlug($prefixSlug);
        } catch (ORMException $e) {
            $this->prefix = $this->initNewPrefix($prefixSlug);
        }

        return $this;
    }

    /**
     * Initialize a new prefix in the DB.
     *
     * @param $prefixSlug
     *
     * @return CacheEngineDatabasePrefix
     */
    protected function initNewPrefix($prefixSlug)
    {
        $em = $this->getEntityManager();

        $prefix = new CacheEngineDatabasePrefix();
        $prefix->setSlug($prefixSlug);

        $em->persist($prefix);
        $em->flush();

        return $prefix;
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
     * Check if the handler type is supported using the default decider implementation.
     *
     * @param mixed,... $by
     *
     * @return bool
     */
    protected function isSupportedDefaultDecider(...$by)
    {
        if (false === $this->isEnabled()) {
            return false;
        }

        $hasValidOrmExtension = Extension::areAnyEnabled(
            'mysql', 'mysqli', 'pdo_mysql', 'pgsql', 'pdo_pgsql', 'mongo'
        );

        return (bool) $hasValidOrmExtension;
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

        return ($item instanceof CacheEngineDatabaseItem ? $item->getValue() : null);
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
            $item = new CacheEngineDatabaseItem();
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
        return (bool) ($this->hasDBCacheItem($key) instanceof CacheEngineDatabaseItem ?: false);
    }

    /**
     * @param $key
     *
     * @return null|CacheEngineDatabaseItem
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

        if (($item = $this->hasDBCacheItem($key)) instanceof CacheEngineDatabaseItem) {
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
        return (bool) ($this->itemRepo->deleteAllByPrefix($this->prefix) > 0 ?: false);
    }
}

/* EOF */
