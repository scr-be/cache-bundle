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

use Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeFilesystem;
use Scribe\CacheBundle\KeyGenerator\KeyGenerator;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorInterface;
use Scribe\Utility\UnitTest\AbstractMantleTestCase;

/**
 * Class HandlerTypeFilesystem.
 */
class HandlerTypeFilesystemTest extends AbstractMantleTestCase
{
    const FULLY_QUALIFIED_CLASS_NAME = 'Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeFilesystem';

    /**
     * @var HandlerTypeFilesystem
     */
    public $type;

    /**
     * @var resource
     */
    public $testResource;

    public function setUp()
    {
        parent::setUp();

        $this->type = $this->getNewHandlerType();
        $this->testResource = fopen(__FILE__, 'r');
    }

    public function getNewHandlerType()
    {
        return $this->getNewHandlerTypeEmpty(new KeyGenerator());
    }

    public function getNewHandlerTypeEmpty(KeyGeneratorInterface $keyGenerator = null, $ttl = 1800, $priority = null, $disabled = false, callable $supportedDecider = null)
    {
        return new HandlerTypeFilesystem($keyGenerator, $ttl, $priority, $disabled, $supportedDecider);
    }

    public function getNewHandlerTypeNotSupported(KeyGeneratorInterface $keyGenerator = null, $ttl = 1800, $priority = null, $disabled = false)
    {
        $supportedDecider = function () { return false; };

        return $this->getNewHandlerTypeEmpty(new KeyGenerator(), 1800, 1, false, $supportedDecider);
    }

    /**
     * @expectedException        Scribe\CacheBundle\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Cannot attempt to get a cached value without setting a key to retrieve it.
     */
    public function testGetWithoutKeyExceptionHandling()
    {
        $this
            ->getNewHandlerType()
            ->get()
        ;
    }

    /**
     * @expectedException        Scribe\CacheBundle\Exceptions\RuntimeException
     * @expectedExceptionMessage You cannot cache a resource data type.
     */
    public function testSetCacheValueAsResourceExceptionHandling()
    {
        $this
            ->getNewHandlerType()
            ->setKey('a', 'b', 'c')
            ->set($this->testResource)
        ;
    }

    public function testToString()
    {
        $this->assertEquals(self::FULLY_QUALIFIED_CLASS_NAME, (string) $this->type);
        $this->assertEquals(self::FULLY_QUALIFIED_CLASS_NAME, $this->type->__toString());
    }

    public function testGetType()
    {
        $this->assertEquals('filesystem', $this->type->getType());
        $this->assertEquals(
            self::FULLY_QUALIFIED_CLASS_NAME,
            $this->type->getType(true)
        );
    }

    public function testIsSupportedDecider()
    {
        $type = $this->getNewHandlerTypeNotSupported();

        $this->assertFalse($type->isSupported());
    }

    public function tearDown()
    {
        fclose($this->testResource);

        parent::tearDown();
    }
}

/* EOF */
