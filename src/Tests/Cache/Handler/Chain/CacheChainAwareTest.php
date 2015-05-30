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

use Scribe\CacheBundle\Cache\Handler\Chain\CacheChain;
use Scribe\CacheBundle\Cache\Handler\Chain\CacheChainMock;
use Scribe\CacheBundle\Tests\Cache\Handler\Chain\Fixtures\CacheChainAware;
use Scribe\Utility\UnitTest\AbstractMantleTestCase;

/**
 * Class CacheChainAwareDeprecatedTest.
 */
class CacheChainAwareTest extends AbstractMantleTestCase
{
    /**
     * @var ChainChainAware
     */
    public $chainAware;

    public function setUp()
    {
        parent::setUp();

        $this->chainAware = new CacheChainAware();
    }

    /**
     * @group CacheChainAware
     */
    public function testGettersAndSetters()
    {
        $chainHandler = new CacheChain();

        static::assertFalse($this->chainAware->hasCacheChain());

        $this->chainAware->setCacheChain($chainHandler);

        static::assertTrue($this->chainAware->hasCacheChain());
        static::assertEquals($chainHandler, $this->chainAware->getCacheChain());
    }

    /**
     * @group CacheChainAware
     */
    public function testExceptionOnInvalidGet()
    {
        $this->setUp();
        $chainHandler = new CacheChainMock();

        static::assertFalse($this->chainAware->hasCacheChain());

        static::assertInstanceOf('Scribe\CacheBundle\Cache\Handler\Chain\CacheChainMock', $this->chainAware->getCacheChain()->setKey('does-not-matter'));

        static::assertFalse($this->chainAware->hasCacheChain());
        static::assertEquals($chainHandler, $this->chainAware->getCacheChain());
        static::assertInstanceOf('Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineMock', $this->chainAware->getCacheChain()->getActiveHandler());

        static::assertFalse($this->chainAware->getCacheChain()->hasActiveHandler());

        $this->chainAware->getCacheChain()->addHandler($this->chainAware->getCacheChain()->getActiveHandler());
        static::assertFalse($this->chainAware->getCacheChain()->hasActiveHandler());
    }
}

/* EOF */
