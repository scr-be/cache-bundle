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
use Scribe\Doctrine\Exception\ORMException;
use Scribe\Doctrine\Exception\ORMExceptionInterface;

/**
 * Class CacheDBHandlerPrefixRepository.
 */
class CacheDBHandlerPrefixRepository extends EntityRepository
{
    /**
     * @param string $slug
     *
     * @throws ORMException
     *
     * @return mixed
     */
    public function findOneBySlug($slug)
    {
        $q = $this
            ->createQueryBuilder('p')
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->setMaxResults(1)
            ->getQuery()
        ;

        try {
            $result = $q->getSingleResult();
        } catch (\Exception $e) {
            throw new ORMException(
                sprintf(
                    'Could not fetch the requested prefix "%s".',
                    $slug
                ),
                ORMExceptionInterface::CODE_GENERIC,
                $e
            );
        }

        return $result;
    }
}

/* EOF */
