<?php

/*
 * This file is part of the Teavee Block Manager Bundle.
 *
 * (c) Scribe Inc.     <oss@scr.be>
 * (c) Rob Frawley 2nd <rmf@scr.be>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\Teavee\ObjectCacheBundle\Tests\DependencyInjection\Aware;

use Scribe\Teavee\ObjectCacheBundle\DependencyInjection\Aware\CacheManagerAwareTrait;
use Scribe\WonkaBundle\Utility\TestCase\KernelTestCase;
use Scribe\Teavee\ObjectCacheBundle\Component\Manager\CacheManager;

/**
 * Class CacheManagerAwareTraitTest.
 */
class CacheManagerAwareTraitTest extends KernelTestCase
{
    /**
     * @var CacheManager
     */
    public static $m;

    /**
     * @var CacheManagerAwareTrait
     */
    public static $a;

    public function setUp()
    {
        parent::setUp();

        self::$m = self::$staticContainer->get('s.cache');
        self::$a = $this->getMockBuilder('Scribe\Teavee\ObjectCacheBundle\DependencyInjection\Aware\CacheManagerAwareTrait')
            ->getMockForTrait();
    }

    public function testGetEmptyException()
    {
        $this->setExpectedException('Scribe\Wonka\Exception\RuntimeException');

        self::$a->getCache();
    }

    public function testGetterAndSetters()
    {
        $a = self::$a;

        static::assertNull($a->getCacheManager());
        static::assertFalse($a->isCacheAvailable());
        $a->setCacheManager(self::$m);
        static::assertInstanceOf('Scribe\Teavee\ObjectCacheBundle\Component\Cache\CacheAttendantInterface', $a->getCache());
        static::assertInstanceOf('Scribe\Teavee\ObjectCacheBundle\Component\Manager\CacheManagerInterface', $a->getCacheManager());
        static::assertTrue($a->isCacheAvailable());
    }
}

/* EOF */
