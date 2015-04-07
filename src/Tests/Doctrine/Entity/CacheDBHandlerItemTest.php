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

use Scribe\CacheBundle\Doctrine\Entity\Cache\CacheDBHandlerItem;
use Scribe\CacheBundle\Doctrine\Entity\Cache\CacheDBHandlerPrefix;
use Scribe\Utility\UnitTest\AbstractMantleKernelTestCase;

/**
 * Class CacheDBHandlerItemTest.
 */
class CacheDBHandlerItemTest extends AbstractMantleKernelTestCase
{
    public function testMutators()
    {
        $entity = new CacheDBHandlerItem();
        $prefixEntity = new CacheDBHandlerPrefix();

        $entity
            ->setPrefix($prefixEntity)
            ->setK('another')
            ->setValue('thing')
            ->setTtl(100)
        ;

        $this->assertEquals($prefixEntity, $entity->getPrefix());
        $this->assertEquals('another', $entity->getK());
        $this->assertEquals('thing', $entity->getValue());
        $this->assertEquals(100, $entity->getTtl());

        $this->assertEquals('Scribe\CacheBundle\Doctrine\Entity\Cache\CacheDBHandlerItem:not-em-managed', (string) $entity);
        $entity->clearTtl();
        $entity->clearK();
        $this->assertNull($entity->getTtl());
        $this->assertNull($entity->getK());
    }
}

/* EOF */
