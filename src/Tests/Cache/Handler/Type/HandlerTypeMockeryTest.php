<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Tests\Cache\Handler\Type;

use PHPUnit_Framework_TestCase;
use Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeMockery;
use Scribe\CacheBundle\KeyGenerator\KeyGenerator;

/**
 * Class HandlerTypeMockeryTest.
 */
class HandlerTypeMockeryTest extends PHPUnit_Framework_TestCase
{
    const FULLY_QUALIFIED_CLASS_NAME = 'Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeMockery';

    /**
     * @var HandlerTypeMockery
     */
    public $type;

    public function setUp()
    {
        $this->type = $this->getNewHandlerType();
    }

    public function getNewHandlerType()
    {
        return new HandlerTypeMockery(new KeyGenerator());
    }

    public function testSetKey()
    {
        $keys = [1, 2, 3];
        $this->assertEquals($this->type, $this->type->setKey(...$keys));
    }

    public function testGetKey()
    {
        $this->assertNull($this->type->getKey());
    }

    public function testHasKey()
    {
        $this->assertTrue($this->type->hasKey());
    }

    public function testIsSupported()
    {
        $this->assertTrue($this->type->isSupported());
    }

    public function testSupportedDecider()
    {
        $this->type->setSupportedDecider(function () { return false; });

        $this->assertFalse($this->type->isSupported());

        $this->type->unsetSupportedDecider();

        $this->assertTrue($this->type->isSupported());
    }

    public function testToString()
    {
        $this->assertEquals(self::FULLY_QUALIFIED_CLASS_NAME, (string) $this->type);
        $this->assertEquals(self::FULLY_QUALIFIED_CLASS_NAME, $this->type->__toString());
    }

    public function testGetType()
    {
        $this->assertEquals('mockery', $this->type->getType());
        $this->assertEquals(
            self::FULLY_QUALIFIED_CLASS_NAME,
            $this->type->getType(true)
        );
    }
}

/* EOF */
