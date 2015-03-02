<?php
/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Tests;

use PHPUnit_Framework_TestCase;
use ReflectionClass;
use Scribe\CacheBundle\ScribeCacheBundle;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ScribeCacheBundleTest
 *
 * @package Scribe\CacheBundle\Tests
 */
class ScribeCacheBundleTest extends PHPUnit_Framework_TestCase
{
    const FULLY_QUALIFIED_CLASS_NAME = 'Scribe\CacheBundle\ScribeCacheBundle';

    private $container;

    protected function setUp()
    {
        $kernel = new \AppKernel('test', true);
        $kernel->boot();

        $this->container = $kernel->getContainer();
    }

    protected function getNewBundle()
    {
        return new ScribeCacheBundle;
    }

    protected function getReflection()
    {
        return new ReflectionClass(self::FULLY_QUALIFIED_CLASS_NAME);
    }

    public function testCanBuildContainer()
    {
        $this->assertTrue(($this->container instanceof Container));
    }

    public function testCanAccessContainerServices()
    {
        $this->assertTrue($this->container->has('scribe_cache.key_generator'));
    }

    public function testCanApplyCompilerPass()
    {
        $this->assertTrue($this->container->has('scribe_cache.handler_chain'));

        $methodChain = $this->container->get('scribe_cache.handler_chain');

        $this->assertNotEquals([ ], $methodChain->getHandlers());
        $this->assertTrue($methodChain->hasHandlers());
        $this->assertEquals(3, count($methodChain->getHandlers()));
        var_dump($methodChain->getChosenHandlerName());
    }
}

/* EOF */
