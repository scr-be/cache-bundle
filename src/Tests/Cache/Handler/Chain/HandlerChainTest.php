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
use Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeDB;
use Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeFilesystem;
use Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeMemcached;
use Scribe\CacheBundle\KeyGenerator\KeyGenerator;
use Scribe\Utility\UnitTest\AbstractMantleKernelTestCase;

/**
 * Class HandlerChainTest.
 */
class HandlerChainTest extends AbstractMantleKernelTestCase
{
    const FULLY_QUALIFIED_CLASS_NAME = 'Scribe\CacheBundle\Tests\Cache\Handler\Chain\HandlerChain';

    protected $handlerChain;

    public function setUp()
    {
        parent::setUp();

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
            new HandlerTypeMemcached(new KeyGenerator(), 1800, 11),
            new HandlerTypeFilesystem(new KeyGenerator(), 1800, 20)
        );

        return $chain;
    }

    protected function getNewHandlerChainWithConflictingHandlerPriorities($disabled = false)
    {
        $chain = $this->setHandlerTypesToChain(
            $this->getNewHandlerChain($disabled),
            new HandlerTypeMemcached(new KeyGenerator(), 1800, 2),
            new HandlerTypeFilesystem(new KeyGenerator(), 1800, 2)
        );

        return $chain;
    }

    protected function getNewHandlerChainWithMemcachedHandlerType($disabled = false)
    {
        $chain = $this->setHandlerTypesToChain(
            $this->getNewHandlerChain($disabled),
            new HandlerTypeMemcached(new KeyGenerator())
        );

        return $chain;
    }

    protected function getNewHandlerChainWithFilesystemHandlerType($disabled = false)
    {
        $filesystemHandlerType = new HandlerTypeFilesystem(new KeyGenerator());
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

    public function testChainFromContainer()
    {
        $chain = $this->container->get('s.cache.handler_chain');
        $chain->reDetermineActiveHandler('memcached');
        $this->assertEquals('memcached', $chain->getActiveHandler()->getType());
        $chain->reDetermineActiveHandler('MemCacheD');
        $this->assertEquals('memcached', $chain->getActiveHandler()->getType());
        $type = new HandlerTypeMemcached();
        $chain->reDetermineActiveHandler($type);
        $this->assertEquals('memcached', $chain->getActiveHandler()->getType());

        $chain->reDetermineActiveHandler('db');
        $this->assertEquals('db', $chain->getActiveHandler()->getType());
        $chain->reDetermineActiveHandler('DB');
        $this->assertEquals('db', $chain->getActiveHandler()->getType());
        $type = new HandlerTypeDB();
        $chain->reDetermineActiveHandler($type);
        $this->assertEquals('db', $chain->getActiveHandler()->getType());

        $chain->reDetermineActiveHandler('filesystem');
        $this->assertEquals('filesystem', $chain->getActiveHandler()->getType());
        $chain->reDetermineActiveHandler('FileSystem');
        $this->assertEquals('filesystem', $chain->getActiveHandler()->getType());
        $type = new HandlerTypeFilesystem();
        $chain->reDetermineActiveHandler($type);
        $this->assertEquals('filesystem', $chain->getActiveHandler()->getType());
    }

    public function testChainExceptionFromContainerWithInvalidForcedHandler()
    {
        $this->setExpectedException(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            'Could not find requested cache handler type "invalid-chain-handler".'
        );

        $chain = $this->container->get('s.cache.handler_chain');
        $chain->reDetermineActiveHandler('invalid-chain-handler');
    }

    public function testChainExceptionFromContainerWithInvalidHandlerGetRequest()
    {
        $this->setExpectedException(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            'The requested handler type "invalid-chain-handler" is not available.'
        );

        $chain = $this->container->get('s.cache.handler_chain');
        $chain->getHandler('invalid-chain-handler');
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

    public function testChainHandlerDefaultPriorities()
    {
        $chain = $this->container->get('s.cache.handler_chain');
        $handlers = $chain->getHandlerCollection();

        $this->assertEquals(3, count($handlers));
        $this->assertEquals('memcached', $handlers[1]->getType());
        $this->assertEquals('db', $handlers[2]->getType());
        $this->assertEquals('filesystem', $handlers[3]->getType());
    }

    public function testChainHandlerRePrioritize()
    {
        $chain = $this->container->get('s.cache.handler_chain');
        $handlers = $chain->getHandlerCollection();

        $this->assertEquals(3, count($handlers));
        $this->assertEquals('memcached', $handlers[1]->getType());
        $this->assertEquals('db', $handlers[2]->getType());
        $this->assertEquals('filesystem', $handlers[3]->getType());

        $this->assertEquals('memcached', $chain->getActiveHandler()->getType());

        $chain->getHandler('memcached')->setSupportedDecider(function () { return false; });
        $chain->reDetermineActiveHandler();
        $this->assertEquals('db', $chain->getActiveHandler()->getType());

        $chain->getHandler('db')->setSupportedDecider(function () { return false; });
        $chain->reDetermineActiveHandler();
        $this->assertEquals('filesystem', $chain->getActiveHandler()->getType());

        $chain->getHandler('memcached')->unsetSupportedDecider();
        $chain->reDetermineActiveHandler();
        $this->assertEquals('memcached', $chain->getActiveHandler()->getType());

        $this->setExpectedException(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            'No enabled and supported cache handler types have been configured. '.
            'You must configure at least one type or globally disable this bundle.'
        );

        $chain->getHandler('memcached')->setSupportedDecider(function () { return false; });
        $chain->getHandler('filesystem')->setSupportedDecider(function () { return false; });
        $chain->reDetermineActiveHandler();
        $chain->getActiveHandler();
    }

    public function testNoActiveHandlerIsSupported()
    {
        $chain = $this->getNewHandlerChainWithNoHandlerTypes(true);

        $this->assertTrue($chain->getActiveHandler()->isSupported());
    }

    public function testNoActiveHandlerExceptionHandling()
    {
        $this->setExpectedException(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            'No enabled and supported cache handler types have been configured. You must configure at least one type or globally disable this bundle.'
        );

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
        $this->assertEquals('scribe_cache---1a0f618cfb0e759487cb8a0edef79f57', $chain->getKey());

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

    public function testHandlerChainCanExcludeHandlerViaConfig()
    {
        $this->setExpectedException(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            'No enabled and supported cache handler types have been configured. You must configure at least one type or globally disable this bundle.'
        );

        $chain = $this->getNewHandlerChainWithNoHandlerTypes(false);
        $chain->set('some-value', 'the', 'string', 'for', 'key');
    }

    public function testHandlerChainWithConflictingHandlerPrioritiesExceptionHandling()
    {
        $this->setExpectedException(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            'A duplicate priority of 2 cannot be set for filesystem. Please review your config.'
        );

        $this->getNewHandlerChainWithConflictingHandlerPriorities();
    }

    public function testHandlerChainGetExceptionIncorrectParamCount()
    {
        $this->setExpectedException(
            'Scribe\Exception\InvalidArgumentException',
            'Invalid number of arguments provided to "getHandler" in "Scribe\CacheBundle\Cache\Handler\Chain\AbstractHandlerChain".'
        );

        $chain = $this->getNewHandlerChain(false);
        $chain->getHandler(1, 2, 3);
    }

    public function testSetHandlers()
    {
        $chain = $this->getNewHandlerChain(false);
        $this->assertFalse($chain->hasHandlers());
        $chain->setHandlerCollection([
            new HandlerTypeMemcached(new KeyGenerator()),
            new HandlerTypeFilesystem(new KeyGenerator()),
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
        $object = new \stdClass();
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
        $object = new \stdClass();
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
        $object = new \stdClass();
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

        $object = new \stdClass();
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

    public function testFilesystemHandlerCanChangeTtl()
    {
        $chain = $this->getNewHandlerChainWithFilesystemHandlerType();
        $chain->setTtl(8);

        $this->assertEquals(8, $chain->getTtl());

        $val1 = $key1 = [1, 2, 3];
        $val2 = $key2 = [2, 3, 4];

        $chain->set($val1, ...$key1);

        $chain->setTtl(2);

        $chain->set($val2, ...$key2);

        $this->assertTrue($chain->has(...$key1));
        $this->assertTrue($chain->has(...$key2));
        $this->assertEquals($val1, $chain->get(...$key1));
        $this->assertEquals($val2, $chain->get(...$key2));

        sleep(4);

        $this->assertFalse($chain->has(...$key2));
        $this->assertNull($chain->get(...$key2));

        $chain->setTtl(8);
        sleep(6);

        $this->assertFalse($chain->has(...$key1));
        $this->assertNull($chain->get(...$key1));

        $chain->setTtlToDefault();

        $this->assertEquals(1800, $chain->getTtl());
    }

    public function tearDown()
    {
        $cacheDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'scribe_cache';
        if (is_dir($cacheDir)) {
            $this->removeDirectory($cacheDir);
        }

        parent::tearDown();
    }
}

/* EOF */
