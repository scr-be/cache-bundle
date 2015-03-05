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
        $this->handlerChain = $this->getNewHandlerChainWithAllHandlerTypes();
    }

    protected function getNewHandlerChain($disabled = false)
    {
        return new HandlerChain($disabled);
    }

    protected function setHandlerTypesToChain($chain, ...$types)
    {
        foreach ($types as $priority => $type) {
            $chain->addHandler($type, $priority);
        }

        return $chain;
    }

    protected function getNewHandlerChainWithAllHandlerTypes($disabled = false)
    {
        $chain = $this->setHandlerTypesToChain(
            $this->getNewHandlerChain($disabled),
            new HandlerTypeMemcached(new KeyGenerator, 1800, 11),
            new HandlerTypeFilesystem(new KeyGenerator, 1800, 20)
        );

        return $chain;
    }

    protected function getNewHandlerChainWithConflictingHandlerPriorities($disabled = false)
    {
        $chain = $this->setHandlerTypesToChain(
            $this->getNewHandlerChain($disabled),
            new HandlerTypeMemcached(new KeyGenerator, 1800, 2),
            new HandlerTypeFilesystem(new KeyGenerator, 1800, 2)
        );

        return $chain;
    }

    protected function getNewHandlerChainWithMemcachedHandlerType($disabled = false)
    {
        $chain = $this->setHandlerTypesToChain(
            $this->getNewHandlerChain($disabled),
            new HandlerTypeMemcached(new KeyGenerator)
        );

        return $chain;
    }

    protected function getNewHandlerChainWithFilesystemHandlerType($disabled = false)
    {
        $filesystemHandlerType = new HandlerTypeFilesystem(new KeyGenerator);
        $filesystemHandlerType->proposeCacheDirectory('/tmp');

        $chain = $this->setHandlerTypesToChain(
            $this->getNewHandlerChain($disabled),
            $filesystemHandlerType
        );

        return $chain;
    }

    protected function getNewHandlerChainWithNoHandlerTypes($disabled = false)
    {
        $chain = $this->setHandlerTypesToChain(
            $this->getNewHandlerChain($disabled)
        );

        return $chain;
    }

    public function testHasPriority()
    {
        $chain = $this->getNewHandlerChainWithAllHandlerTypes();

        $this->assertTrue($chain->getActiveHandler()->hasPriority());
    }

    public function testEnsureDefaultChainHasHandler()
    {
        $this->assertTrue($this->handlerChain->hasHandlers());
    }

    public function testActiveHandlerIsFilesystem()
    {
        $chain = $this->getNewHandlerChainWithFilesystemHandlerType();

        $this->assertEquals('filesystem', $chain->getActiveHandlerType());
    }

    public function testNoActiveHandler()
    {
        $chain = $this->getNewHandlerChainWithNoHandlerTypes(true);

        $this->assertFalse($chain->isEnabled());
        $this->assertFalse($chain->hasHandlers());
        $this->assertFalse($chain->del(1, 2, 3));
        $this->assertFalse($chain->flushAll());
    }

    public function testNoActiveHandlerIsSupported()
    {
        $chain = $this->getNewHandlerChainWithNoHandlerTypes(true);

        $this->assertTrue($chain->getActiveHandler()->isSupported());
    }

    /**
     * @expectedException        Scribe\CacheBundle\Exceptions\RuntimeException
     * @expectedExceptionMessage No enabled and supported cache handler types have been configured. You must configure at least one type or globally disable this bundle.
     */
    public function testNoActiveHandlerExceptionHandling()
    {
        $chain = $this->getNewHandlerChainWithNoHandlerTypes();
        $chain->setKey('one', 'two', 'three');
    }

    public function testGetActiveHandlerTypeForMemcached()
    {
        $chain = $this->getNewHandlerChainWithMemcachedHandlerType();

        $this->assertEquals('memcached', $chain->getActiveHandlerType());
        $this->assertEquals(
            'Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeMemcached',
            $chain->getActiveHandlerType(true)
        );
    }

    public function testHandlerChainMutatorKey()
    {
        $chain = $this->getNewHandlerChainWithAllHandlerTypes();
        $this->assertFalse($chain->hasKey());

        $chain->setKey('one', 'two', 'three');
        $this->assertEquals('scribe_cache---ace501ae86c283fb26d52cddb7dc807c', $chain->getKey());

        $chain->setEnabled(false);
        $this->assertNull($chain->getKey());
    }

    public function testHandlerChainWhenDisabled()
    {
        $chain = $this->getNewHandlerChainWithAllHandlerTypes();
        $chain->setEnabled(false);

        $this->assertEquals($chain, $chain->setKey('one', 'two', 'three'));
        $this->assertFalse($chain->set('some-data'));
        $this->assertNull($chain->get());
        $this->assertFalse($chain->has());
    }

    public function testHandlerChainDoesNotHaveKey()
    {
        $chain = $this->getNewHandlerChainWithAllHandlerTypes();

        $this->assertFalse($chain->has(rand()));
    }

    /**
     * @expectedException             Scribe\CacheBundle\Exceptions\RuntimeException
     * @expectedExceptionMessageRegex There are no valid cache handler configured..*
     */
    public function testHandlerChainCanExcludeHandlerViaConfig()
    {
        $chain = $this->getNewHandlerChainWithNoHandlerTypes(false);
        $chain->set('some-value', 'the', 'string', 'for', 'key');
    }

    /**
     * @expectedException        Scribe\CacheBundle\Exceptions\RuntimeException
     * @expectedExceptionMessage A duplicate priority of 2 cannot be set for filesystem. Please review your config.
     */
    public function testHandlerChainWithConflictingHandlerPrioritiesExceptionHandling()
    {
        $this->getNewHandlerChainWithConflictingHandlerPriorities();
    }

    public function testSetHandlers()
    {
        $chain = $this->getNewHandlerChain($disabled = false);
        $this->assertFalse($chain->hasHandlers());
        $chain->setHandlers([
            new HandlerTypeMemcached(new KeyGenerator),
            new HandlerTypeFilesystem(new KeyGenerator)
        ]);
        $this->assertTrue($chain->hasHandlers());
    }

    public function testFilesystemHandlerCanCache()
    {
        $chain = $this->getNewHandlerChainWithFilesystemHandlerType();
        $chain->set('random-string', 'the-key-to-random-string');

        $this->assertEquals('random-string', $chain->get());
    }

    public function testFilesystemHandlerCanCache2()
    {
        $chain  = $this->getNewHandlerChainWithFilesystemHandlerType();
        $object = new \stdClass;
        $object->name = 'test field';

        $chain
            ->setKey('a', 'random', 'key', 1, 2, 3, ['an', 'array', 'of', 'items'], $object)
            ->set('random-string-2')
        ;

        $this->assertEquals('random-string-2', $chain->get());
        $this->assertEquals('random-string-2', $chain->get(
            'a', 'random', 'key', 1, 2, 3, ['an', 'array', 'of', 'items'], $object
        ));
    }

    public function testFilesystemHandlerCanCache3()
    {
        $chain  = $this->getNewHandlerChainWithFilesystemHandlerType();
        $object = new \stdClass;
        $object->name = 'test field';

        $chain->set('random-string-2', 'a', 'random', 'key', 1, 2, 3, ['an', 'array', 'of', 'items'], $object);

        $this->assertEquals('random-string-2', $chain->get());
        $this->assertEquals('random-string-2', $chain->get(
            'a', 'random', 'key', 1, 2, 3, ['an', 'array', 'of', 'items'], $object
        ));
    }

    public function testFilesystemHandlerCanCache4()
    {
        $chain  = $this->getNewHandlerChainWithFilesystemHandlerType();
        $object = new \stdClass;
        $object->name = 'test field 2';

        $chain->setKey('a', 'random', 'key', 1, 2, 3, $object);

        $this->assertNull($chain->get());

        $chain->set('random-string-3');
        $this->assertNotNull($chain->get());
        $this->assertEquals('random-string-3', $chain->get());
        $this->assertEquals('random-string-3', $chain->get(
            'a', 'random', 'key', 1, 2, 3, $object
        ));
    }

    public function testFilesystemHandlerCanCacheAndDelete()
    {
        $chain  = $this->getNewHandlerChainWithFilesystemHandlerType();
        $chain->flushAll();

        $object = new \stdClass;
        $object->name = 'test field 2';

        $chain->setKey('a', 'random', 'key', 1, 2, 3, $object);

        $this->assertNull($chain->get());

        $chain->set('random-string-3');
        $this->assertNotNull($chain->get());
        $this->assertEquals('random-string-3', $chain->get());
        $this->assertEquals('random-string-3', $chain->get(
            'a', 'random', 'key', 1, 2, 3, $object
        ));

        $this->assertTrue($chain->del());
        sleep(1);
        $this->assertNull($chain->get());
    }

    public function testFilesystemHandlerCanCacheAndFlushAll()
    {
        $chain = $this->getNewHandlerChainWithFilesystemHandlerType();

        $val1 = $key1 = [1, 2, 3];
        $val2 = $key2 = [2, 3, 4];
        $val3 = $key3 = [3, 4, 5];

        $chain->set($val1, ...$key1);
        $chain->set($val2, ...$key2);
        $chain->set($val3, ...$key3);

        $this->assertEquals($val1, $chain->get(...$key1));
        $this->assertEquals($val2, $chain->get(...$key2));
        $this->assertEquals($val3, $chain->get(...$key3));

        $chain->flushAll();

        $this->assertNull($chain->get(...$key1));
        $this->assertNull($chain->get(...$key2));
        $this->assertNull($chain->get(...$key3));
    }

    public function testFilesystemHandlerCanCacheAndDeleteWhenStale()
    {
        $chain = $this->getNewHandlerChainWithFilesystemHandlerType();
        $chain->getActiveHandler()->setTtl(1);

        $val1 = $key1 = [1, 2, 3];

        $chain->set($val1, ...$key1);

        $this->assertEquals($val1, $chain->get(...$key1));

        sleep(2);

        $this->assertFalse($chain->has(...$key1));

        $this->assertNull($chain->get(...$key1));
    }

    public function tearDown()
    {
        $tempDirBase = sys_get_temp_dir();
        $tempDir     = $tempDirBase . DIRECTORY_SEPARATOR . 'scribe_cache';

        if (false === is_dir($tempDir)) {

            return;
        }
        $kg = new KeyGenerator;
        $files = glob($tempDir . '/scribe*');
        foreach ($files as $f) {
            if (substr($f, 0, 1) == '.') {
                continue;
            }
            unlink($f);
        }

        rmdir($tempDir);
    }
}

/* EOF */
