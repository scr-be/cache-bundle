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
use Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineDatabase;
use Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineFilesystem;
use Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineMemcached;
use Scribe\CacheBundle\KeyGenerator\KeyGenerator;
use Scribe\Utility\UnitTest\AbstractMantleKernelTestCase;

/**
 * Class CacheChainTest.
 */
class CacheChainTest extends AbstractMantleKernelTestCase
{
    const FULLY_QUALIFIED_CLASS_NAME = 'Scribe\CacheBundle\Tests\Cache\Handler\Chain\CacheChain';

    protected $handlerChain;

    public function setUp()
    {
        parent::setUp();

        $this->handlerChain = $this->getNewHandlerChainWithAllHandlerTypes();
    }

    protected function getNewHandlerChain($disabled = false)
    {
        return new CacheChain($disabled);
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
            new CacheEngineMemcached(new KeyGenerator(), 1800, 11),
            new CacheEngineFilesystem(new KeyGenerator(), 1800, 20)
        );

        return $chain;
    }

    protected function getNewHandlerChainWithConflictingHandlerPriorities($disabled = false)
    {
        $chain = $this->setHandlerTypesToChain(
            $this->getNewHandlerChain($disabled),
            new CacheEngineMemcached(new KeyGenerator(), 1800, 2),
            new CacheEngineFilesystem(new KeyGenerator(), 1800, 2)
        );

        return $chain;
    }

    protected function getNewHandlerChainWithMemcachedHandlerType($disabled = false)
    {
        $chain = $this->setHandlerTypesToChain(
            $this->getNewHandlerChain($disabled),
            new CacheEngineMemcached(new KeyGenerator())
        );

        return $chain;
    }

    protected function getNewHandlerChainWithFilesystemHandlerType($disabled = false)
    {
        $filesystemHandlerType = new CacheEngineFilesystem(new KeyGenerator());
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

    /**
     * @group CacheChain
     */
    public function testChainFromContainer()
    {
        $chain = $this->container->get('s.cache.chain');
        $chain->reDetermineActiveHandler('memcached');
        static::assertEquals('memcached', $chain->getActiveHandler()->getType());
        $chain->reDetermineActiveHandler('MemCacheD');
        static::assertEquals('memcached', $chain->getActiveHandler()->getType());
        $type = new CacheEngineMemcached();
        $chain->reDetermineActiveHandler($type);
        static::assertEquals('memcached', $chain->getActiveHandler()->getType());

        $chain->reDetermineActiveHandler('database');
        static::assertEquals('database', $chain->getActiveHandler()->getType());
        $chain->reDetermineActiveHandler('DaTaBaSE');
        static::assertEquals('database', $chain->getActiveHandler()->getType());
        $type = new CacheEngineDatabase();
        $chain->reDetermineActiveHandler($type);
        static::assertEquals('database', $chain->getActiveHandler()->getType());

        $chain->reDetermineActiveHandler('filesystem');
        static::assertEquals('filesystem', $chain->getActiveHandler()->getType());
        $chain->reDetermineActiveHandler('FileSystem');
        static::assertEquals('filesystem', $chain->getActiveHandler()->getType());
        $type = new CacheEngineFilesystem();
        $chain->reDetermineActiveHandler($type);
        static::assertEquals('filesystem', $chain->getActiveHandler()->getType());
    }

    /**
     * @group CacheChain
     */
    public function testChainExceptionFromContainerWithInvalidForcedHandler()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            '#Could not find requested cache handler type "invalid-chain-handler" in .*#'
        );

        $chain = $this->container->get('s.cache.chain');
        $chain->reDetermineActiveHandler('invalid-chain-handler');
    }

    /**
     * @group CacheChain
     */
    public function testChainExceptionFromContainerWithInvalidHandlerGetRequest()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            '#The requested handler type "invalid-chain-handler" is not available in .*#'
        );

        $chain = $this->container->get('s.cache.chain');
        $chain->getHandler('invalid-chain-handler');
    }

    /**
     * @group CacheChain
     */
    public function testHasPriority()
    {
        $chain = $this->getNewHandlerChainWithAllHandlerTypes();

        static::assertTrue($chain->getActiveHandler()->hasPriority());
    }

    /**
     * @group CacheChain
     */
    public function testEnsureDefaultChainHasHandler()
    {
        static::assertTrue($this->handlerChain->hasHandlerCollection());
    }

    /**
     * @group CacheChain
     */
    public function testActiveHandlerIsFilesystem()
    {
        $chain = $this->getNewHandlerChainWithFilesystemHandlerType();

        static::assertEquals('filesystem', $chain->getActiveHandlerType());
    }

    /**
     * @group CacheChain
     */
    public function testNoActiveHandler()
    {
        $chain = $this->getNewHandlerChainWithNoHandlerTypes(true);

        static::assertFalse($chain->isEnabled());
        static::assertFalse($chain->hasHandlerCollection());
        static::assertFalse($chain->del(1, 2, 3));
        static::assertFalse($chain->flushAll());
    }

    /**
     * @group CacheChain
     */
    public function testChainHandlerDefaultPriorities()
    {
        $chain = $this->container->get('s.cache.chain');
        $handlers = $chain->getHandlerCollection();

        static::assertEquals(3, count($handlers));
        static::assertEquals('memcached', $handlers[1]->getType());
        static::assertEquals('database', $handlers[2]->getType());
        static::assertEquals('filesystem', $handlers[3]->getType());
    }

    /**
     * @group CacheChain
     */
    public function testChainHandlerRePrioritize()
    {
        $chain = $this->container->get('s.cache.chain');
        $handlers = $chain->getHandlerCollection();

        static::assertEquals(3, count($handlers));
        static::assertEquals('memcached', $handlers[1]->getType());
        static::assertEquals('database', $handlers[2]->getType());
        static::assertEquals('filesystem', $handlers[3]->getType());

        static::assertEquals('memcached', $chain->getActiveHandler()->getType());

        $chain->getHandler('memcached')->setSupportedDecider(function () { return false; });
        $chain->reDetermineActiveHandler();
        static::assertEquals('database', $chain->getActiveHandler()->getType());

        $chain->getHandler('database')->setSupportedDecider(function () { return false; });
        $chain->reDetermineActiveHandler();
        static::assertEquals('filesystem', $chain->getActiveHandler()->getType());

        $chain->getHandler('memcached')->clearSupportedDecider();
        $chain->reDetermineActiveHandler();
        static::assertEquals('memcached', $chain->getActiveHandler()->getType());

        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            '#No enabled/supported cache engines are configured; you must configure at least one or globally disable this bundle .*#'
        );

        $chain->getHandler('memcached')->setSupportedDecider(function () { return false; });
        $chain->getHandler('filesystem')->setSupportedDecider(function () { return false; });
        $chain->reDetermineActiveHandler();
        $chain->getActiveHandler();
    }

    /**
     * @group CacheChain
     */
    public function testNoActiveHandlerIsSupported()
    {
        $chain = $this->getNewHandlerChainWithNoHandlerTypes(true);

        static::assertTrue($chain->getActiveHandler()->isSupported());
    }

    /**
     * @group CacheChain
     */
    public function testNoActiveHandlerExceptionHandling()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            '#No enabled/supported cache engines are configured; you must configure at least one or globally disable this bundle .*#'
        );

        $chain = $this->getNewHandlerChainWithNoHandlerTypes();
        $chain->setKey('one', 'two', 'three');
    }

    /**
     * @group CacheChain
     */
    public function testGetActiveHandlerTypeForMemcached()
    {
        $chain = $this->getNewHandlerChainWithMemcachedHandlerType();

        static::assertEquals('memcached', $chain->getActiveHandlerType());
        static::assertEquals(
            'Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineMemcached',
            $chain->getActiveHandlerType(true)
        );
    }

    /**
     * @group CacheChain
     */
    public function testHandlerChainMutatorKey()
    {
        $chain = $this->getNewHandlerChainWithAllHandlerTypes();
        static::assertFalse($chain->hasKey());

        $chain->setKey('one', 'two', 'three');
        static::assertEquals('scribe_cache---1a0f618cfb0e759487cb8a0edef79f57', $chain->getKey());

        $chain->setEnabled(false);
        static::assertNull($chain->getKey());
    }

    /**
     * @group CacheChain
     */
    public function testHandlerChainWhenDisabled()
    {
        $chain = $this->getNewHandlerChainWithAllHandlerTypes();
        $chain->setEnabled(false);

        static::assertEquals($chain, $chain->setKey('one', 'two', 'three'));
        static::assertFalse($chain->set('some-data'));
        static::assertNull($chain->get());
        static::assertFalse($chain->has());
    }

    /**
     * @group CacheChain
     */
    public function testHandlerChainDoesNotHaveKey()
    {
        $chain = $this->getNewHandlerChainWithAllHandlerTypes();

        static::assertFalse($chain->has(rand()));
    }

    /**
     * @group CacheChain
     */
    public function testHandlerChainCanExcludeHandlerViaConfig()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            '#No enabled/supported cache engines are configured; you must configure at least one or globally disable this bundle .*#'
        );

        $chain = $this->getNewHandlerChainWithNoHandlerTypes(false);
        $chain->set('some-value', 'the', 'string', 'for', 'key');
    }

    /**
     * @group CacheChain
     */
    public function testHandlerChainWithConflictingHandlerPrioritiesExceptionHandling()
    {
        $this->setExpectedException(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            'A duplicate priority of 2 cannot be set for filesystem. Please review your config.'
        );

        $this->getNewHandlerChainWithConflictingHandlerPriorities();
    }

    /**
     * @group CacheChain
     */
    public function testHandlerChainGetExceptionIncorrectParamCount()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\InvalidArgumentException',
            '#Invalid number of arguments provided to "getHandler" in .*#'
        );

        $chain = $this->getNewHandlerChain(false);
        $chain->getHandler(1, 2, 3);
    }

    /**
     * @group CacheChain
     */
    public function testSetHandlers()
    {
        $chain = $this->getNewHandlerChain(false);
        static::assertFalse($chain->hasHandlerCollection());
        $chain->setHandlerCollection([
            new CacheEngineMemcached(new KeyGenerator()),
            new CacheEngineFilesystem(new KeyGenerator()),
        ]);
        static::assertTrue($chain->hasHandlerCollection());
    }

    /**
     * @group CacheChain
     */
    public function testFilesystemHandlerCanCache()
    {
        $chain = $this->getNewHandlerChainWithFilesystemHandlerType();
        $chain->set('random-string', 'the-key-to-random-string');

        static::assertEquals('random-string', $chain->get());
    }

    /**
     * @group CacheChain
     */
    public function testFilesystemHandlerCanCache2()
    {
        $chain  = $this->getNewHandlerChainWithFilesystemHandlerType();
        $object = new \stdClass();
        $object->name = 'test field';

        $chain
            ->setKey('a', 'random', 'key', 1, 2, 3, ['an', 'array', 'of', 'items'], $object)
            ->set('random-string-2')
        ;

        static::assertEquals('random-string-2', $chain->get());
        static::assertEquals('random-string-2', $chain->get(
            'a', 'random', 'key', 1, 2, 3, ['an', 'array', 'of', 'items'], $object
        ));
    }

    /**
     * @group CacheChain
     */
    public function testFilesystemHandlerCanCache3()
    {
        $chain  = $this->getNewHandlerChainWithFilesystemHandlerType();
        $object = new \stdClass();
        $object->name = 'test field';

        $chain->set('random-string-2', 'a', 'random', 'key', 1, 2, 3, ['an', 'array', 'of', 'items'], $object);

        static::assertEquals('random-string-2', $chain->get());
        static::assertEquals('random-string-2', $chain->get(
            'a', 'random', 'key', 1, 2, 3, ['an', 'array', 'of', 'items'], $object
        ));
    }

    /**
     * @group CacheChain
     */
    public function testFilesystemHandlerCanCache4()
    {
        $chain  = $this->getNewHandlerChainWithFilesystemHandlerType();
        $object = new \stdClass();
        $object->name = 'test field 2';

        $chain->setKey('a', 'random', 'key', 1, 2, 3, $object);

        static::assertNull($chain->get());

        $chain->set('random-string-3');
        static::assertNotNull($chain->get());
        static::assertEquals('random-string-3', $chain->get());
        static::assertEquals('random-string-3', $chain->get(
            'a', 'random', 'key', 1, 2, 3, $object
        ));
    }

    /**
     * @group CacheChain
     */
    public function testFilesystemHandlerCanCacheAndDelete()
    {
        $chain  = $this->getNewHandlerChainWithFilesystemHandlerType();
        $chain->flushAll();

        $object = new \stdClass();
        $object->name = 'test field 2';

        $chain->setKey('a', 'random', 'key', 1, 2, 3, $object);

        static::assertNull($chain->get());

        $chain->set('random-string-3');
        static::assertNotNull($chain->get());
        static::assertEquals('random-string-3', $chain->get());
        static::assertEquals('random-string-3', $chain->get(
            'a', 'random', 'key', 1, 2, 3, $object
        ));

        static::assertTrue($chain->del());
        sleep(1);
        static::assertNull($chain->get());
    }

    /**
     * @group CacheChain
     */
    public function testFilesystemHandlerCanCacheAndFlushAll()
    {
        $chain = $this->getNewHandlerChainWithFilesystemHandlerType();

        $val1 = $key1 = [1, 2, 3];
        $val2 = $key2 = [2, 3, 4];
        $val3 = $key3 = [3, 4, 5];

        $chain->set($val1, ...$key1);
        $chain->set($val2, ...$key2);
        $chain->set($val3, ...$key3);

        static::assertEquals($val1, $chain->get(...$key1));
        static::assertEquals($val2, $chain->get(...$key2));
        static::assertEquals($val3, $chain->get(...$key3));

        $chain->flushAll();

        static::assertNull($chain->get(...$key1));
        static::assertNull($chain->get(...$key2));
        static::assertNull($chain->get(...$key3));
    }

    /**
     * @group CacheChain
     */
    public function testFilesystemHandlerCanCacheAndDeleteWhenStale()
    {
        $chain = $this->getNewHandlerChainWithFilesystemHandlerType();
        $chain->getActiveHandler()->setTtl(1);

        $val1 = $key1 = [1, 2, 3];

        $chain->set($val1, ...$key1);

        static::assertEquals($val1, $chain->get(...$key1));

        sleep(2);

        static::assertFalse($chain->has(...$key1));

        static::assertNull($chain->get(...$key1));
    }

    /**
     * @group CacheChain
     */
    public function testFilesystemHandlerCanChangeTtl()
    {
        $chain = $this->getNewHandlerChainWithFilesystemHandlerType();
        $chain->setTtl(8);

        static::assertEquals(8, $chain->getTtl());

        $val1 = $key1 = [1, 2, 3];
        $val2 = $key2 = [2, 3, 4];

        $chain->set($val1, ...$key1);

        $chain->setTtl(2);

        $chain->set($val2, ...$key2);

        static::assertTrue($chain->has(...$key1));
        static::assertTrue($chain->has(...$key2));
        static::assertEquals($val1, $chain->get(...$key1));
        static::assertEquals($val2, $chain->get(...$key2));

        sleep(4);

        static::assertFalse($chain->has(...$key2));
        static::assertNull($chain->get(...$key2));

        $chain->setTtl(8);
        sleep(6);

        static::assertFalse($chain->has(...$key1));
        static::assertNull($chain->get(...$key1));

        $chain->setTtlToDefault();

        static::assertEquals(1800, $chain->getTtl());
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
