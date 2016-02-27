<?php

/*
 * This file is part of the Teavee Object Caching Bundle.
 *
 * (c) Scribe Inc.     <oss@scr.be>
 * (c) Rob Frawley 2nd <rmf@scr.be>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\Teavee\ObjectCacheBundle\Tests\Component\Generator\Manager;

use Scribe\Teavee\ObjectCacheBundle\Component\Cache\Memcached\MemcachedAttendant;
use Scribe\WonkaBundle\Utility\TestCase\KernelTestCase;
use Scribe\Teavee\ObjectCacheBundle\Component\Manager\CacheManager;

/**
 * Class CacheManagerTest.
 */
class CacheManagerTest extends KernelTestCase
{
    /**
     * @var CacheManager
     */
    public static $m;

    public function setUp()
    {
        parent::setUp();

        self::$m = self::$staticContainer->get('s.cache');
        self::$m = self::$staticContainer->get('s.cache');
    }

    public function testInterface()
    {
        self::assertInstanceOf('Scribe\\Teavee\\ObjectCacheBundle\\Component\\Manager\\CacheManagerInterface', self::$m);
    }

    public function testActiveCache()
    {
        self::assertInstanceOf('Scribe\\Teavee\\ObjectCacheBundle\\Component\\Cache\\Memcached\\MemcachedAttendant', self::$m->getActive());
    }

    public function testDisabledGlobal()
    {
        self::assertTrue(self::$m->isEnabled());
        self::$m->setEnabled(false);
        self::assertFalse(self::$m->isEnabled());
        self::assertNull(self::$m->getActive());
    }

    public function testDisabledMemcached()
    {
        self::assertTrue(self::$m->getActive()->isEnabled());
        self::$m->getActive()->setEnabled(false);
        self::assertFalse(self::$m->getActive()->isEnabled());
        self::$m->determineActive();
        self::assertFalse(self::$m->getActive() instanceof MemcachedAttendant);
    }

    public function testSetActive()
    {
        self::assertTrue(self::$m->getActive()->isEnabled());
        self::assertTrue(self::$m->setActive(0));
        self::assertTrue(self::$m->getActive()->isEnabled());
        self::assertTrue(self::$m->setActive(1));
        self::assertTrue(self::$m->getActive()->isEnabled());
        self::assertFalse(self::$m->setActive(2));
    }
}

/* EOF */
