<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Tests\Cache\Handler\Type;

use Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabaseItem;
use Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabasePrefix;
use Scribe\Utility\UnitTest\AbstractMantleKernelTestCase;

/**
 * Class CacheEngineDatabaseItemTest.
 */
class CacheEngineDatabaseItemTest extends AbstractMantleKernelTestCase
{
    /**
     * @group CacheEntity
     */
    public function testMutators()
    {
        $entity = new CacheEngineDatabaseItem();
        $prefixEntity = new CacheEngineDatabasePrefix();

        $entity
            ->setPrefix($prefixEntity)
            ->setK('another')
            ->setValue('thing')
            ->setTtl(100)
        ;

        static::assertEquals($prefixEntity, $entity->getPrefix());
        static::assertEquals('another', $entity->getK());
        static::assertEquals('thing', $entity->getValue());
        static::assertEquals(100, $entity->getTtl());

        static::assertEquals('Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabaseItem:not-em-managed', (string) $entity);
        $entity->clearTtl();
        $entity->clearK();
        static::assertNull($entity->getTtl());
        static::assertNull($entity->getK());
    }
}

/* EOF */
