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

    public function test_interface()
    {
        self::assertInstanceOf('Scribe\\Teavee\\ObjectCacheBundle\\Component\\Manager\\CacheManagerInterface', self::$m);
    }

    public function test_active_cache()
    {
        self::assertInstanceOf('Scribe\\Teavee\\ObjectCacheBundle\\Component\\Cache\\Memcached\\MemcachedAttendant', self::$m->getActive());
    }

    public function test_disabled_global()
    {
        self::assertTrue(self::$m->isEnabled());
        self::$m->setEnabled(false);
        self::assertFalse(self::$m->isEnabled());
        self::assertNull(self::$m->getActive());
    }

    public function test_disabled_memcached()
    {
        self::assertTrue(self::$m->getActive()->isEnabled());
        self::$m->getActive()->setEnabled(false);
        self::assertFalse(self::$m->getActive()->isEnabled());
        self::$m->determineActive();
        self::assertNull(self::$m->getActive());
    }

    public function test_set_active()
    {
        self::assertTrue(self::$m->getActive()->isEnabled());
        self::assertTrue(self::$m->setActive(0));
        self::assertTrue(self::$m->getActive()->isEnabled());
        self::assertFalse(self::$m->setActive(1));
    }
}

/* EOF */
