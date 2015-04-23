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
use Scribe\CacheBundle\Doctrine\Entity\Cache\CacheDBHandlerItem;
use Scribe\CacheBundle\Doctrine\Entity\Cache\CacheDBHandlerPrefix;
use Scribe\Doctrine\Exception\ORMException;
use Scribe\Utility\UnitTest\AbstractMantleKernelTestCase;

/**
 * Class CacheDBHandlerPrefixTest.
 */
class CacheDBHandlerPrefixTest extends AbstractMantleKernelTestCase
{
    const FULLY_QUALIFIED_CLASS_NAME = 'Scribe\CacheBundle\Doctrine\Entity\Cache\CacheDBHandlerPrefix';

    public function testMutators()
    {
        $itemEntity = new CacheDBHandlerItem();
        $entity = new CacheDBHandlerPrefix();

        $this->assertEquals('Scribe\CacheBundle\Doctrine\Entity\Cache\CacheDBHandlerPrefix:not-em-managed', (string) $entity);
    }

    public function testItemHandling()
    {
        $entity = new CacheDBHandlerPrefix();
        $refFormat = new \ReflectionClass($entity);
        $prop = $refFormat->getProperty('items');
        $prop->setAccessible(true);

        $items = new ArrayCollection([new CacheDBHandlerItem()]);

        $this->assertFalse($entity->hasItem($items->first()));

        $prop->setValue($entity, $items);

        $this->assertEquals($items, $entity->getItems());
        $this->assertEquals(1, $entity->getItems()->count());
        $this->assertTrue($entity->hasItem($items->first()));
    }

    public function testSlugException()
    {
        $this->setExpectedException(
            'Scribe\Doctrine\Exception\SubscriberEventORMException',
            'This entity does not support automatically generating slugs!',
            ORMException::CODE_ORM_GENERIC
        );

        $entity = new CacheDBHandlerPrefix();
        $entity->getAutoSlugFields();
    }
}

/* EOF */
