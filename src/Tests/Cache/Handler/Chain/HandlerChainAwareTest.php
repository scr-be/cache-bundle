<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Tests\Cache\Handler\Chain;

use Scribe\CacheBundle\Cache\Handler\Chain\HandlerChain;
use Scribe\CacheBundle\Tests\Cache\Handler\Chain\Fixtures\ObjectChainAware;
use Scribe\Utility\UnitTest\AbstractMantleTestCase;

/**
 * Class HandlerChainAwareTest.
 */
class HandlerChainAwareTest extends AbstractMantleTestCase
{
    /**
     * @var ObjectChainAware
     */
    public $chainAware;

    public function setUp()
    {
        parent::setUp();

        $this->chainAware = new ObjectChainAware();
    }

    public function testGettersAndSetters()
    {
        $chainHandler = new HandlerChain();

        $this->assertFalse($this->chainAware->hasCacheHandlerChain());

        $this->chainAware->setCacheHandlerChain($chainHandler);

        $this->assertTrue($this->chainAware->hasCacheHandlerChain());
        $this->assertEquals($chainHandler, $this->chainAware->getCacheHandlerChain());
    }

    public function testExceptionOnInvalidGet()
    {
        $this->setUp();
        $chainHandler = new HandlerChain();

        $this->assertFalse($this->chainAware->hasCacheHandlerChain());

        $this->setExpectedException(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            'You requested a cache chain handler via the method getCacheHandlerChain declared in trait Scribe\CacheBundle\Cache\Handler\Chain\HandlerChainAwareTrait and used in Scribe\CacheBundle\Tests\Cache\Handler\Chain\Fixtures\ObjectChainAware, but no handler chain has been set.'
        );
        $this->chainAware->getCacheHandlerChain()->setKey('does-not-matter');

        $this->chainAware->setCacheHandlerChain($chainHandler);

        $this->assertTrue($this->chainAware->hasCacheHandlerChain());
        $this->assertEquals($chainHandler, $this->chainAware->getCacheHandlerChain());
    }
}

/* EOF */
