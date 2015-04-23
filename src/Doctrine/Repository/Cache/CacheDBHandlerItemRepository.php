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
use Scribe\CacheBundle\Doctrine\Entity\Cache\CacheDBHandlerItem;
use Scribe\CacheBundle\Doctrine\Entity\Cache\CacheDBHandlerPrefix;
use Scribe\Doctrine\Exception\ORMException;

/**
 * Class CacheDBHandlerItemRepository.
 */
class CacheDBHandlerItemRepository extends EntityRepository
{
    /**
     * @param CacheDBHandlerPrefix $prefix
     * @param string               $key
     *
     * @throws ORMException
     *
     * @return CacheDBHandlerItem
     */
    public function findOneByPrefixAndKey(CacheDBHandlerPrefix $prefix, $key)
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
                sprintf(
                    'Could not fetch the requested cache item for the provided key "%s" and prefix "%s".',
                    $key,
                    $prefix->getSlug()
                ),
                ORMException::CODE_ORM_GENERIC,
                $e
            );
        }

        return $result;
    }

    /**
     * @param CacheDBHandlerPrefix $prefix
     *
     * @return array
     */
    public function findByPrefix(CacheDBHandlerPrefix $prefix)
    {
        $q = $this
            ->createQueryBuilder('i')
            ->where('i.prefix = :prefix')
            ->setParameter('prefix', $prefix)
            ->getQuery()
        ;

        $results = $q->getResult();

        return $results;
    }
}

/* EOF */
