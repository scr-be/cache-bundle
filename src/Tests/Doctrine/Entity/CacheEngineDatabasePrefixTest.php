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

use Doctrine\Common\Collections\ArrayCollection;
use Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabaseItem;
use Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabasePrefix;
use Scribe\Doctrine\Exception\ORMException;
use Scribe\Utility\UnitTest\AbstractMantleKernelTestCase;

/**
 * Class CacheEngineDatabasePrefixTest.
 */
class CacheEngineDatabasePrefixTest extends AbstractMantleKernelTestCase
{
    const FULLY_QUALIFIED_CLASS_NAME = 'Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabasePrefix';

    /**
     * @group CacheEntity
     */
    public function testMutators()
    {
        $entity = new CacheEngineDatabasePrefix();

        static::assertEquals('Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabasePrefix:not-em-managed', (string) $entity);
    }

    /**
     * @group CacheEntity
     */
    public function testItemHandling()
    {
        $entity = new CacheEngineDatabasePrefix();
        $refFormat = new \ReflectionClass($entity);
        $prop = $refFormat->getProperty('items');
        $prop->setAccessible(true);

        $items = new ArrayCollection([new CacheEngineDatabaseItem()]);

        static::assertFalse($entity->hasItem($items->first()));

        $prop->setValue($entity, $items);

        static::assertEquals($items, $entity->getItems());
        static::assertEquals(1, $entity->getItems()->count());
        static::assertTrue($entity->hasItem($items->first()));
    }

    /**
     * @group CacheEntity
     */
    public function testSlugException()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\Doctrine\Exception\SubscriberEventORMException',
            '#This entity does not support automatically generating slugs in .*#',
            ORMException::CODE_ORM_GENERIC
        );

        $entity = new CacheEngineDatabasePrefix();
        $entity->getAutoSlugFields();
    }
}

/* EOF */
