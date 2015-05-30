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
use Scribe\CacheBundle\Tests\Cache\Handler\Chain\Fixtures\CacheChainAwareDeprecated;
use Scribe\Utility\UnitTest\AbstractMantleTestCase;

/**
 * Class CacheChainAwareDeprecatedTest.
 */
class CacheChainAwareDeprecatedTest extends AbstractMantleTestCase
{
    /**
     * @var CacheChainAwareDeprecated
     */
    public $chainAware;

    public function setUp()
    {
        parent::setUp();

        $this->chainAware = new CacheChainAwareDeprecated();
    }

    /**
     * @group CacheChainAware
     */
    public function testGettersAndSetters()
    {
        $chainHandler = new CacheChain();

        static::assertFalse($this->chainAware->hasCacheHandlerChain());

        $this->chainAware->setCacheHandlerChain($chainHandler);

        static::assertTrue($this->chainAware->hasCacheHandlerChain());
        static::assertEquals($chainHandler, $this->chainAware->getCacheHandlerChain());
    }

    /**
     * @group CacheChainAware
     */
    public function testExceptionOnInvalidGet()
    {
        $this->setUp();
        $chainHandler = new CacheChain();

        static::assertFalse($this->chainAware->hasCacheHandlerChain());

        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            '#You requested a cache chain handler via the method getCacheHandlerChain declared in trait .* but no handler chain has been set#'
        );
        $this->chainAware->getCacheHandlerChain()->setKey('does-not-matter');

        $this->chainAware->setCacheHandlerChain($chainHandler);

        static::assertTrue($this->chainAware->hasCacheHandlerChain());
        static::assertEquals($chainHandler, $this->chainAware->getCacheHandlerChain());
    }
}

/* EOF */
