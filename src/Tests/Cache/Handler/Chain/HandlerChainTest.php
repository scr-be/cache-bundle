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

use PHPUnit_Framework_TestCase;
use Scribe\CacheBundle\Cache\Handler\Chain\HandlerChain;
use Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeApcu;
use Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeFilesystem;
use Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeMemcached;
use Scribe\CacheBundle\KeyGenerator\KeyGenerator;

/**
 * Class HandlerChainTest
 *
 * @package Scribe\CacheBundle\Tests\Cache\Handler\Chain
 */
class HandlerChainTest extends PHPUnit_Framework_TestCase
{
    const FULLY_QUALIFIED_CLASS_NAME = 'Scribe\CacheBundle\Tests\Cache\Handler\Chain\HandlerChain';

    protected $handlerChain;

    protected function setUp()
    {
        $this->handlerChain = $this->getNewHandlerChain();
        $this->assignHandlerTypesToChain(
            new HandlerTypeApcu(new KeyGenerator),
            new HandlerTypeMemcached(new KeyGenerator),
            new HandlerTypeFilesystem(new KeyGenerator)
        );
    }

    protected function getNewHandlerChain()
    {
        return new HandlerChain;
    }

    protected function assignHandlerTypesToChain(...$types)
    {
        foreach ($types as $i => $t) {
            $this->handlerChain->addHandler($t, $i);
        }
    }

    public function testHasHandlers()
    {
        $this->assertTrue($this->handlerChain->hasHandlers());
    }

    public function testActiveHandlerIsFilesystem()
    {
        $this->assertEquals('HandlerTypeFilesystem', $this->handlerChain->getChosenHandlerName());
    }

    public function testFilesystemHandlerCanCache()
    {
        $this->handlerChain->set('random-string', 'the-key-to-random-string');
        $this->assertEquals('random-string', $this->handlerChain->get());
    }

    public function testFilesystemHandlerCanCache2()
    {
        $obj = new \stdClass;
        $obj->name = 'test field';

        $this
            ->handlerChain
            ->setKey('a', 'random', 'key', 1, 2, 3, ['an', 'array', 'of', 'items'], $obj)
            ->set('random-string-2')
        ;

        $this->assertEquals('random-string-2', $this->handlerChain->get());
        $this->assertEquals('random-string-2', $this->handlerChain->get(
            'a', 'random', 'key', 1, 2, 3, ['an', 'array', 'of', 'items'], $obj
        ));
    }

    public function testFilesystemHandlerCanCache3()
    {
        $obj = new \stdClass;
        $obj->name = 'test field';

        $this
            ->handlerChain
            ->set('random-string-2', 'a', 'random', 'key', 1, 2, 3, ['an', 'array', 'of', 'items'], $obj)
        ;

        $this->assertEquals('random-string-2', $this->handlerChain->get());
        $this->assertEquals('random-string-2', $this->handlerChain->get(
            'a', 'random', 'key', 1, 2, 3, ['an', 'array', 'of', 'items'], $obj
        ));
    }

    public function testFilesystemHandlerCanCache4()
    {
        $obj = new \stdClass;
        $obj->name = 'test field 2';

        $this
            ->handlerChain
            ->setKey('a', 'random', 'key', 1, 2, 3, $obj)
        ;

        $this->assertNull($this->handlerChain->get());
        $this->handlerChain->set('random-string-3');
        $this->assertNotNull($this->handlerChain->get());
        $this->assertEquals('random-string-3', $this->handlerChain->get());
        $this->assertEquals('random-string-3', $this->handlerChain->get(
            'a', 'random', 'key', 1, 2, 3, $obj
        ));
    }

    public function tearDown()
    {
        $tempDirBase = sys_get_temp_dir();
        $tempDir     = $tempDirBase . DIRECTORY_SEPARATOR . 'scribe_cache';

        $cacheFiles = scandir($tempDir);

        foreach ($cacheFiles as $f) {
            if (substr($f, 0, 1) == '.') {
                continue;
            }

            unlink($tempDir . DIRECTORY_SEPARATOR . $f);
        }
    }

}

/* EOF */
