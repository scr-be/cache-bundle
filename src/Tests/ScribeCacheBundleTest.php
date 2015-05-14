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

use PHPUnit_Framework_TestCase;
use ReflectionClass;
use Scribe\CacheBundle\ScribeCacheBundle;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ScribeCacheBundleTest.
 */
class ScribeCacheBundleTest extends PHPUnit_Framework_TestCase
{
    const FULLY_QUALIFIED_CLASS_NAME = 'Scribe\CacheBundle\ScribeCacheBundle';

    /**
     * @var ContainerInterface
     */
    private $container;

    public function setUp()
    {
        $kernel = new \AppKernel('test', true);
        $kernel->boot();
        $this->container = $kernel->getContainer();
    }

    public function getNewBundle()
    {
        return new ScribeCacheBundle();
    }

    public function getReflection()
    {
        return new ReflectionClass(self::FULLY_QUALIFIED_CLASS_NAME);
    }

    public function testCanBuildContainer()
    {
        static::assertTrue(($this->container instanceof Container));
    }

    public function testCanAccessContainerServices()
    {
        static::assertTrue($this->container->has('s.cache.chain'));
    }

    public function testCanApplyCompilerPass()
    {
        /*
        $this->assertTrue($this->container->has('s.cache.handler_chain'));
        $methodChain = $this->container->get('s.cache.handler_chain');
        $this->assertNotEquals([], $methodChain->getHandlerCollection());
        $this->assertTrue($methodChain->hasHandlers());
        $this->assertEquals(3, count($methodChain->getHandlerCollection()));
        */
    }
}

/* EOF */
