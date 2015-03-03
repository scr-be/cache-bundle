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
use Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeFilesystem;
use Scribe\CacheBundle\KeyGenerator\KeyGenerator;

/**
 * Class HandlerTypeFilesystem
 *
 * @package Scribe\CacheBundle\Tests\Cache\Handler\Type
 */
class HandlerTypeFilesystemTest extends PHPUnit_Framework_TestCase
{
    const FULLY_QUALIFIED_CLASS_NAME = 'Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeFilesystem';

    protected $type;

    protected $testResource;

    protected function setUp()
    {
        $this->type = $this->getNewHandlerType();
        $this->testResource = fopen(__FILE__, 'r');
    }

    protected function getNewHandlerType()
    {
        return new HandlerTypeFilesystem(new KeyGenerator);
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
    }


    protected function tearDown()
    {
        fclose($this->testResource);
    }
}

/* EOF */
