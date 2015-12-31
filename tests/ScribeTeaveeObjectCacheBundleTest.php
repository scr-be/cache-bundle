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

namespace Scribe\Teavee\ObjectCacheBundle\Tests;

use Scribe\Wonka\Utility\UnitTest\WonkaTestCase;

/**
 * Class ScribeTeaveeObjectCacheBundleTest.
 */
class ScribeTeaveeObjectCacheBundleTest extends WonkaTestCase
{
    /**
     * @var \AppKernel
     */
    public static $kernel;

    public function setUp()
    {
        $kernel = new \AppKernel('test', true);
        $kernel->boot();

        self::$kernel = $kernel;
    }

    public function tearDown()
    {
        if (self::$kernel instanceof \AppKernel) {
            self::$kernel->shutdown();
        }
    }

    public function test_kernel_build_container()
    {
        static::assertInstanceOf('Symfony\Component\DependencyInjection\ContainerInterface', self::$kernel->getContainer());
    }

    public function test_has_cache_service()
    {
        static::assertTrue(self::$kernel->getContainer()->has('s.cache'));
    }

    public function test_cache_compiler_pass()
    {
        static::assertTrue(self::$kernel->getContainer()->has('s.teavee_object_cache.registrar'));
        $registrar = self::$kernel->getContainer()->get('s.teavee_object_cache.registrar');

        static::assertInstanceOf('Scribe\Teavee\ObjectCacheBundle\DependencyInjection\Compiler\Registrar\CacheCompilerRegistrar', $registrar);
        static::assertCount(2, $registrar->getAttendantCollection());

        static::assertTrue(self::$kernel->getContainer()->has('s.teavee_object_cache.key_generator'));
        $g = self::$kernel->getContainer()->get('s.teavee_object_cache.key_generator');
        foreach ($registrar->getAttendantCollection() as $attendant) {
            static::assertEquals($g, $attendant->getKeyGenerator());
        }
    }
}

/* EOF */
