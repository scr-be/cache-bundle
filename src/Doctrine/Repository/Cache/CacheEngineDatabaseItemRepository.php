<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Doctrine\Repository\Cache;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabaseItem;
use Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabasePrefix;
use Scribe\Doctrine\Exception\ORMException;

/**
 * Class CacheEngineDatabaseItemRepository.
 */
class CacheEngineDatabaseItemRepository extends EntityRepository
{
    /**
     * @param CacheEngineDatabasePrefix $prefix
     * @param string               $key
     *
     * @throws ORMException
     *
     * @return CacheEngineDatabaseItem
     */
    public function findOneByPrefixAndKey(CacheEngineDatabasePrefix $prefix, $key)
    {
        $q = $this
            ->createQueryBuilder('i')
            ->where('i.prefix = :prefix')
            ->andWhere('i.slug = :slug')
            ->setParameter('prefix', $prefix)
            ->setParameter('slug', $key)
            ->setMaxResults(1)
            ->getQuery()
        ;

        try {
            $result = $q->getSingleResult();
        } catch (\Exception $e) {
            throw new ORMException(
                'Could not fetch the requested cache item for the provided key "%s" and prefix "%s" in "%s".',
                ORMException::CODE_ORM_GENERIC, $e, null, $key, $prefix->getSlug(), __METHOD__
            );
        }

        return $result;
    }

    /**
     * @param CacheEngineDatabasePrefix $prefix
     *
     * @return int
     */
    public function findStaleCountByPrefix(CacheEngineDatabasePrefix $prefix)
    {
        $qb = $this->createQueryBuilder('item');
        $q = $qb
            ->select('count(item.id)')
            ->where('item.prefix = :prefix')
            ->andWhere($qb->expr()->lte(':datetimenow', '(item.updated_on + item.ttl)'))
            ->setParameter('prefix', $prefix)
            ->setParameter('datetimenow', new \DateTime())
            ->setMaxResults(1)
            ->getQuery()
        ;

        return (int) $q->getSingleScalarResult();
    }

    /**
     * @param CacheEngineDatabasePrefix $prefix
     *
     * @return int
     */
    public function deleteStaleByPrefix(CacheEngineDatabasePrefix $prefix)
    {
        $qb = $this->createQueryBuilder('item');
        $q = $qb
            ->delete()
            ->where('item.prefix = :prefix')
            ->andWhere($qb->expr()->lte(':datetimenow', '(item.updated_on + item.ttl)'))
            ->setParameter('prefix', $prefix)
            ->setParameter('datetimenow', new \DateTime())
            ->getQuery()
        ;

        return (int) $q->getResult();
    }

    /**
     * @param CacheEngineDatabasePrefix $prefix
     *
     * @return int
     */
    public function deleteAllByPrefix(CacheEngineDatabasePrefix $prefix)
    {
        $qb = $this->createQueryBuilder('i');
        $q = $qb
            ->delete()
            ->where('i.prefix = :prefix')
            ->setParameter('prefix', $prefix)
            ->getQuery()
        ;

        return (int) $q->getResult();
    }
}

/* EOF */
