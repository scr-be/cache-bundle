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
use Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineMock;
use Scribe\CacheBundle\KeyGenerator\KeyGenerator;

/**
 * Class CacheEngineMockTest.
 */
class CacheEngineMockTest extends PHPUnit_Framework_TestCase
{
    const FULLY_QUALIFIED_CLASS_NAME = 'Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineMock';

    /**
     * @var CacheEngineMock
     */
    public $type;

    public function setUp()
    {
        $this->type = $this->getNewHandlerType();
    }

    public function getNewHandlerType()
    {
        return new CacheEngineMock(new KeyGenerator());
    }

    /**
     * @group CacheEngine
     * @group CacheEngineMock
     */
    public function testSetKey()
    {
        $keys = [1, 2, 3];
        static::assertEquals($this->type, $this->type->setKey(...$keys));
    }

    /**
     * @group CacheEngine
     * @group CacheEngineMock
     */
    public function testGetKey()
    {
        static::assertNull($this->type->getKey());
    }

    /**
     * @group CacheEngine
     * @group CacheEngineMock
     */
    public function testHasKey()
    {
        static::assertTrue($this->type->hasKey());
    }

    /**
     * @group CacheEngine
     * @group CacheEngineMock
     */
    public function testIsSupported()
    {
        static::assertTrue($this->type->isSupported());
    }

    /**
     * @group CacheEngine
     * @group CacheEngineMock
     */
    public function testSupportedDecider()
    {
        $this->type->setSupportedDecider(function () { return false; });

        static::assertFalse($this->type->isSupported());

        $this->type->clearSupportedDecider();

        static::assertTrue($this->type->isSupported());
    }

    /**
     * @group CacheEngine
     * @group CacheEngineMock
     */
    public function testToString()
    {
        static::assertEquals(self::FULLY_QUALIFIED_CLASS_NAME, (string) $this->type);
        static::assertEquals(self::FULLY_QUALIFIED_CLASS_NAME, $this->type->__toString());
    }

    /**
     * @group CacheEngine
     * @group CacheEngineMock
     */
    public function testGetType()
    {
        static::assertEquals('mock', $this->type->getType());
        static::assertEquals(
            self::FULLY_QUALIFIED_CLASS_NAME,
            $this->type->getType(true)
        );
    }

    /**
     * @group CacheEngine
     * @group CacheEngineMock
     */
    public function testMutators()
    {
        static::assertNull($this->type->get('something'));
        static::assertFalse($this->type->set('something', 'something'));
        static::assertFalse($this->type->has('something'));
        static::assertFalse($this->type->del('something'));
        static::assertFalse($this->type->flushAll());
    }
}

/* EOF */
