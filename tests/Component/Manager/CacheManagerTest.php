<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Tests\Component\Generator\Manager;

use Scribe\WonkaBundle\Utility\TestCase\KernelTestCase;
use Scribe\CacheBundle\Component\Manager\CacheManager;

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
    }

    public function test_interface()
    {
        self::assertInstanceOf('Scribe\\CacheBundle\\Component\\Manager\\CacheManagerInterface', self::$m);
    }

    public function test_active_cache()
    {
        self::assertInstanceOf('Scribe\\CacheBundle\\Component\\Cache\\Memcached\\MemcachedCacheMethod', self::$m->getActive());
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
        self::$m->setActive();
        self::assertNull(self::$m->getActive());
    }
}

/* EOF */
