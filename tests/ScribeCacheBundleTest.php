<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 * (c) Matthias Noback <
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Tests;

use Scribe\Wonka\Utility\UnitTest\WonkaTestCase;

/**
 * Class ScribeCacheBundleTest.
 */
class ScribeCacheBundleTest extends WonkaTestCase
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
        self::$kernel->shutdown();
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
        static::assertTrue(self::$kernel->getContainer()->has('s.cache.registrar'));
        $registrar = self::$kernel->getContainer()->get('s.cache.registrar');

        static::assertInstanceOf('Scribe\CacheBundle\DependencyInjection\Compiler\Registrar\CacheCompilerRegistrar', $registrar);
        static::assertCount(2, $registrar->getAttendantCollection());

        static::assertTrue(self::$kernel->getContainer()->has('s.cache.key_generator'));
        $g = self::$kernel->getContainer()->get('s.cache.key_generator');
        foreach ($registrar->getAttendantCollection() as $attendant) {
            static::assertEquals($g, $attendant->getKeyGenerator());
        }
    }
}

/* EOF */
