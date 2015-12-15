<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Tests\DependencyInjection\Aware;

use Scribe\CacheBundle\DependencyInjection\Aware\CacheManagerAwareTrait;
use Scribe\WonkaBundle\Utility\TestCase\KernelTestCase;
use Scribe\CacheBundle\Component\Manager\CacheManager;

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
        self::$a = $this->getMockBuilder('Scribe\CacheBundle\DependencyInjection\Aware\CacheManagerAwareTrait')
            ->getMockForTrait();
    }

    public function test_get_empty_exception()
    {
        $this->setExpectedException('Scribe\Wonka\Exception\RuntimeException');

        self::$a->getCache();
    }

    public function test_getter_and_setters()
    {
        $a = self::$a;

        static::assertNull($a->getCacheManager());
        static::assertFalse($a->isCacheAvailable());
        $a->setCacheManager(self::$m);
        static::assertInstanceOf('Scribe\CacheBundle\Component\Cache\CacheMethodInterface', $a->getCache());
        static::assertInstanceOf('Scribe\CacheBundle\Component\Manager\CacheManagerInterface', $a->getCacheManager());
        static::assertTrue($a->isCacheAvailable());
    }
}

/* EOF */
