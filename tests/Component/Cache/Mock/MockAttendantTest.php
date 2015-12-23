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

namespace Scribe\Teavee\ObjectCacheBundle\Tests\Component\Generator\KeyGenerator;

use Scribe\WonkaBundle\Utility\TestCase\KernelTestCase;
use Scribe\Teavee\ObjectCacheBundle\Component\Cache\Memcached\MemcachedAttendant;

/**
 * Class MockAttendantTest.
 */
class MockAttendantTest extends KernelTestCase
{
    /**
     * @var MemcachedAttendant
     */
    public static $m;

    public function setUp()
    {
        parent::setUp();

        self::$m = self::$staticContainer->get('s.object_cache.attendant_mock');
    }

    public function test_interface()
    {
        self::assertInstanceOf('Scribe\\WonkaBundle\\Component\\DependencyInjection\\Compiler\\Attendant\\AbstractCompilerAttendant', self::$m);
        self::assertInstanceOf('Scribe\\Teavee\\ObjectCacheBundle\\Component\\Cache\\CacheAttendantInterface', self::$m);
    }

    public function test_is_enabled()
    {
        self::assertFalse(self::$m->isEnabled());
        self::$m->setEnabled(true);
        self::assertTrue(self::$m->isEnabled());
    }

    public function test_is_not_enabled()
    {
        self::assertFalse(self::$m->isEnabled());
        self::$m->setEnabled(false);
        self::assertFalse(self::$m->isEnabled());
    }

    public function test_is_supported()
    {
        self::assertTrue(self::$m->isSupported());
    }

    public function test_basic_caching()
    {
        $key = ['key', 'string'];
        $val = 'value';

        self::$m->setEnabled(true);
        self::assertFalse(self::$m->has(...$key));
        self::assertTrue(self::$m->set($val, ...$key));
        self::assertFalse(self::$m->has(...$key));
        self::assertNull(self::$m->get(...$key));
        self::assertNotEquals($val, self::$m->get(...$key));
        self::assertTrue(self::$m->del(...$key));
        self::assertTrue(self::$m->flush());
    }

    public function test_is_not_enabled_exception()
    {
        self::setExpectedException('Scribe\\Wonka\\Exception\\RuntimeException');
        self::$m->setEnabled(false);
        self::$m->get('some-key');
    }
}

/* EOF */
