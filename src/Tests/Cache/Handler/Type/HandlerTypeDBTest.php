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

use Doctrine\ORM\NoResultException;
use Scribe\Utility\UnitTest\AbstractMantleKernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeDB;
use Scribe\CacheBundle\Cache\Handler\Chain\AbstractHandlerChain;
use Scribe\CacheBundle\KeyGenerator\KeyGenerator;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorInterface;

/**
 * Class HandlerTypeDBTest.
 *
 *
 * @Title("DB Cache Handler Test")
 */
class HandlerTypeDBTest extends AbstractMantleKernelTestCase
{
    const FULLY_QUALIFIED_CLASS_NAME = 'Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeDB';

    /**
     * @var AbstractHandlerChain
     */
    public $chain;

    /**
     * @var HandlerTypeDB
     */
    public $type;

    /**
     * @var ContainerInterface
     */
    public $container;

    /**
     * setUp.
     *
     * @throws \Scribe\CacheBundle\Exceptions\RuntimeException
     */
    public function setUp()
    {
        parent::setUp();

        $this->chain = $this->container->get('s.cache.handler_chain');
        $this->chain->reDetermineActiveHandler('db');
        $this->type = $this->chain->getActiveHandler();
    }

    /**
     * getNewHandlerType.
     *
     * @return HandlerTypeDB
     */
    public function getNewHandlerType()
    {
        return $this->getNewHandlerTypeEmpty(new KeyGenerator());
    }

    /**
     * getNewHandlerTypeEmpty.
     *
     * @param KeyGeneratorInterface $keyGenerator
     * @param int                   $ttl
     * @param null                  $priority
     * @param bool                  $disabled
     * @param callable              $supportedDecider
     *
     * @return HandlerTypeDB
     */
    public function getNewHandlerTypeEmpty(KeyGeneratorInterface $keyGenerator = null, $ttl = 1800, $priority = null, $disabled = false, callable $supportedDecider = null)
    {
        return new HandlerTypeDB($keyGenerator, $ttl, $priority, $disabled, $supportedDecider);
    }

    /**
     * getNewHandlerTypeNotSupported.
     *
     * @param KeyGeneratorInterface $keyGenerator
     * @param int                   $ttl
     * @param null                  $priority
     * @param bool                  $disabled
     *
     * @return HandlerTypeDB
     */
    public function getNewHandlerTypeNotSupported(KeyGeneratorInterface $keyGenerator = null, $ttl = 1800, $priority = null, $disabled = false)
    {
        $supportedDecider = function () { return false; };

        return $this->getNewHandlerTypeEmpty(new KeyGenerator(), 1800, 1, false, $supportedDecider);
    }

    public function testHandlerInNotSupportedState()
    {
        $handler = $this->getNewHandlerTypeEmpty(
            static::$staticContainer->get('s.cache.key_generator'),
            10, 1, false, function() { return false; }
        );

        $handler->initManagerAndRepositories(
            static::$staticContainer->get('doctrine.orm.default_entity_manager'),
            static::$staticContainer->get('s.cache.cache_db_handler_item.repo'),
            static::$staticContainer->get('s.cache.cache_db_handler_prefix.repo')
        );

        static::assertFalse($handler->flushStaleItems());
        static::assertFalse($handler->set('data', 'key'));
        static::assertFalse($handler->has('foo', 'bar'));
        static::assertFalse($handler->del('bar', 'foo'));
        static::assertFalse($handler->flushAll());
    }

    public function testHandlerInDisabledStateWithCustomDecider()
    {
        $handler = $this->getNewHandlerTypeEmpty(
            static::$staticContainer->get('s.cache.key_generator'),
            10, 1, true, function() { return true; }
        );

        $handler->initManagerAndRepositories(
            static::$staticContainer->get('doctrine.orm.default_entity_manager'),
            static::$staticContainer->get('s.cache.cache_db_handler_item.repo'),
            static::$staticContainer->get('s.cache.cache_db_handler_prefix.repo')
        );

        static::assertFalse($handler->isSupported());
        static::assertFalse($handler->flushStaleItems());
        static::assertFalse($handler->set('data', 'key'));
        static::assertFalse($handler->has('foo', 'bar'));
        static::assertFalse($handler->del('bar', 'foo'));
        static::assertFalse($handler->flushAll());
    }

    public function testHandlerInDisabledStateWithDefaultDecider()
    {
        $handler = $this->getNewHandlerTypeEmpty(
            static::$staticContainer->get('s.cache.key_generator'),
            10, 1, true, null
        );

        $handler->initManagerAndRepositories(
            static::$staticContainer->get('doctrine.orm.default_entity_manager'),
            static::$staticContainer->get('s.cache.cache_db_handler_item.repo'),
            static::$staticContainer->get('s.cache.cache_db_handler_prefix.repo')
        );

        static::assertFalse($handler->isSupported());
        static::assertFalse($handler->flushStaleItems());
        static::assertFalse($handler->set('data', 'key'));
        static::assertFalse($handler->has('foo', 'bar'));
        static::assertFalse($handler->del('bar', 'foo'));
        static::assertFalse($handler->flushAll());
    }

    /**
     * @Title("Confirm an exception is thrown when `get` is called with no cache key")
     * @Features({"Exception Handling"})
     * @Stories({"Handler should be able to set/get/has/del/flush"})
     */
    public function testGetWithoutKeyExceptionHandling()
    {
        $this->setExpectedException(
            'Scribe\CacheBundle\Exceptions\InvalidArgumentException',
            'Cannot attempt to get a cached value without setting a key to retrieve it.'
        );

        $this->type->get();
    }

    /**
     * @Title("Attempt to get the fully qualified class name for handler")
     * @Features({"To String", "Self-Aware"})
     * @Stories({"Handler should be able to determine what type it is"})
     */
    public function testToStringFQN()
    {
        static::assertEquals(self::FULLY_QUALIFIED_CLASS_NAME, (string) $this->type);
        static::assertEquals(self::FULLY_QUALIFIED_CLASS_NAME, $this->type->__toString());
    }

    /**
     * @Title("Attempt to get the non-fully qualified class name for handler")
     * @Features({"To String", "Self-Aware"})
     * @Stories({"Handler should be able to determine what type it is"})
     */
    public function testGetType()
    {
        static::assertEquals('db', $this->type->getType());
        static::assertEquals(
            self::FULLY_QUALIFIED_CLASS_NAME,
            $this->type->getType(true)
        );
    }

    public function testRandomInitRepositoryStaleFlush()
    {
        $chain = $this->chain;
        $chain->setTtl(2);

        $val1 = $key1 = [1, 2, 3];
        $val2 = $key2 = [2, 3, 4];
        $val3 = $key3 = [3, 4, 5];

        $chain->set($val1, ...$key1);
        $chain->set($val2, ...$key2);
        $chain->set($val3, ...$key3);

        sleep(2);

        foreach (range(1, 2000) as $i) {
            $this->type->initManagerAndRepositories(
                static::$staticContainer->get('doctrine.orm.default_entity_manager'),
                static::$staticContainer->get('s.cache.cache_db_handler_item.repo'),
                static::$staticContainer->get('s.cache.cache_db_handler_prefix.repo')
            );
        }

        static::assertNotEquals($val1, $chain->get(...$key1));
        static::assertNotEquals($val2, $chain->get(...$key2));
        static::assertNotEquals($val3, $chain->get(...$key3));

        $chain->setTtlToDefault();
    }

    /**
     * @Title("Confirm theDB handler can cache")
     * @Features({"Can Cache"})
     * @Stories({"Handler should be able to set/get/has/del/flush"})
     */
    public function testHandlerCanCacheAndFlushAll()
    {
        $chain = $this->chain;

        $val1 = $key1 = [1, 2, 3];
        $val2 = $key2 = [2, 3, 4];
        $val3 = $key3 = [3, 4, 5];

        $chain->set($val1, ...$key1);
        $chain->set($val2, ...$key2);
        $chain->set($val3, ...$key3);

        static::assertEquals($val1, $chain->get(...$key1));
        static::assertEquals($val2, $chain->get(...$key2));
        static::assertEquals($val3, $chain->get(...$key3));
    }

    /**
     * @Title("Confirm theDB handler can cache")
     * @Features({"Can Cache"})
     * @Stories({"Handler should be able to set/get/has/del/flush"})
     */
    public function testHandlerCanCacheAndFlushStale()
    {
        $chain = $this->chain;
        $chain->setTtl(2);

        $val1 = $key1 = [1, 2, 3];
        $val2 = $key2 = [2, 3, 4];
        $val3 = $key3 = [3, 4, 5];

        $chain->set($val1, ...$key1);
        $chain->set($val2, ...$key2);
        $chain->set($val3, ...$key3);

        static::assertEquals($val1, $chain->get(...$key1));
        static::assertEquals($val2, $chain->get(...$key2));
        static::assertEquals($val3, $chain->get(...$key3));

        sleep(2);

        $chain->getActiveHandler()->flushStaleItems();

        static::assertNotEquals($val1, $chain->get(...$key1));
        static::assertNotEquals($val2, $chain->get(...$key2));
        static::assertNotEquals($val3, $chain->get(...$key3));
    }

    /**
     * @Title("Confirm the DB handler can determine if it has a cached item")
     * @Features({"Can Check Cache"})
     * @Stories({"Handler should be able to set/get/has/del/flush"})
     */
    public function testHandlerCanCacheAndCheck()
    {
        $chain = $this->chain;

        $val1 = $key1 = [81, 82, 83];
        $val2 = $key2 = [82, 83, 84];
        $val3 = $key3 = [83, 84, 85];

        $chain->set($val1, ...$key1);
        $chain->set($val2, ...$key2);
        $chain->set($val3, ...$key3);

        static::assertEquals($val1, $chain->get(...$key1));
        static::assertEquals($val2, $chain->get(...$key2));
        static::assertEquals($val3, $chain->get(...$key3));

        static::assertTrue($chain->has(...$key1));
        static::assertTrue($chain->has(...$key2));
        static::assertTrue($chain->has(...$key3));
    }

    /**
     * @Title("Confirm the DB handler can flush its cache")
     * @Features({"Can Cache", "Can Flush All"})
     * @Stories({"Handler should be able to set/get/has/del/flush"})
     */
    public function testHandlerCanFlushAll()
    {
        $chain = $this->chain;

        $val1 = $key1 = [11, 22, 33];
        $val2 = $key2 = [22, 33, 44];
        $val3 = $key3 = [33, 44, 55];

        $chain->set($val1, ...$key1);
        $chain->set($val2, ...$key2);
        $chain->set($val3, ...$key3);

        static::assertEquals($val1, $chain->get(...$key1));
        static::assertEquals($val2, $chain->get(...$key2));
        static::assertEquals($val3, $chain->get(...$key3));

        static::assertTrue($chain->has(...$key1));
        static::assertTrue($chain->has(...$key2));
        static::assertTrue($chain->has(...$key3));

        $chain->flushAll();

        static::assertNull($chain->get(...$key1));
        static::assertNull($chain->get(...$key2));
        static::assertNull($chain->get(...$key3));

        static::assertFalse($chain->has(...$key1));
        static::assertFalse($chain->has(...$key2));
        static::assertFalse($chain->has(...$key3));
    }

    /**
     * @Title("Confirm the DB handler honors TTL")
     * @Features({"Can Cache", "Can Flush All", "Can Respect TTL"})
     * @Stories({"Handler should be able to set/get/has/del/flush"})
     */
    public function testDBHandlerCanCacheWithValidateTtl()
    {
        $chain = $this->chain;

        $val1 = $key1 = [102, 203, 304];
        $val2 = $key2 = [105, 206, 307];

        $chain->setTtlToDefault();
        $chain->set($val1, ...$key1);
        $chain->setTtl(2);
        $chain->set($val2, ...$key2);

        static::assertEquals($val1, $chain->get(...$key1));
        static::assertTrue($chain->has(...$key1));
        static::assertEquals($val2, $chain->get(...$key2));
        static::assertTrue($chain->has(...$key2));

        sleep(4);

        static::assertEquals($val1, $chain->get(...$key1));
        static::assertTrue($chain->has(...$key1));
        static::assertNotNull($chain->get(...$key1));
        static::assertNotEquals($val2, $chain->get(...$key2));
        static::assertFalse($chain->has(...$key2));
        static::assertNull($chain->get(...$key2));

        $chain->flushAll();

        static::assertNotEquals($val1, $chain->get(...$key1));
        static::assertFalse($chain->has(...$key1));
        static::assertNull($chain->get(...$key1));
        static::assertNotEquals($val2, $chain->get(...$key2));
        static::assertFalse($chain->has(...$key2));
        static::assertNull($chain->get(...$key2));
    }

    /**
     * @Title("Confirm the DB handler can delete cached values")
     * @Features({"Can Cache", "Can Flush All", "Can Respect TTL", "Can Delete"})
     * @Stories({"Handler should be able to set/get/has/del/flush"})
     */
    public function testHandlerCanCacheAndDelete()
    {
        $val1 = $key1 = [1122, 2233, 3344];
        $val2 = $key2 = [2233, 3455, 4455];

        $chain = $this->chain;
        $chain->setTtl(2);

        $chain->set($val1, ...$key1);
        $chain->set($val2, ...$key2);

        static::assertEquals($val1, $chain->get(...$key1));
        static::assertTrue($chain->has(...$key1));

        static::assertEquals($val2, $chain->get(...$key2));
        static::assertTrue($chain->has(...$key2));

        $chain->del(...$key1);

        static::assertNotEquals($val1, $chain->get(...$key1));
        static::assertFalse($chain->has(...$key1));

        sleep(2);

        static::assertFalse($chain->has(...$key2));

        $chain->flushAll();

        static::assertNull($chain->get(...$key1));
        static::assertNull($chain->get(...$key2));
    }

    /**
     * @Title("Check isSupported closure handler")
     * @Features({"Option Handling"})
     * @Stories({"Handler should be able to determine supported state based on passed closure"})
     */
    public function testIsNotSupported()
    {
        static::assertTrue($this->type->isSupported());

        $decider = function () { return (bool) false; };
        $this->type->setSupportedDecider($decider);

        static::assertFalse($this->type->isSupported());
    }

    /**
     * @Title("Check behaviour on internal calls when handler is not supported")
     * @Features({"Option Handling"})
     * @Stories({"Handler should be able to determine supported state based on passed closure"})
     */
    public function testIsNotSupportedInternalCalls()
    {
        $decider = function () { return (bool) false; };
        $this->type->setSupportedDecider($decider);

        static::assertFalse($this->type->isSupported());
        return;

        $this->type->__construct(
            new KeyGenerator()
        );
        $this->type->initManagerAndRepositories(
            $this->container->get('doctrine.orm.entity_manager'),
            $this->container->get('s.cache.cache_db_handler_item.repo'),
            $this->container->get('s.cache.cache_db_handler_prefix.repo')
        );

        $this->type->unsetSupportedDecider();

        static::assertTrue($this->type->isSupported());
    }

    /**
     * @Title("Handler can have TTL values changed randomly and continue to operate properly")
     * @Features({"Can Cache", "Can Respect TTL"})
     * @Stories({"Handler should be able to set/get/has/del/flush"})
     */
    public function testHandlerCanChangeTtl()
    {
        $chain = $this->chain;
        $chain->setTtl(8);

        static::assertEquals(8, $chain->getTtl());

        $val1 = $key1 = [1, 2, 3];
        $val2 = $key2 = [2, 3, 4];

        $chain->set($val1, ...$key1);

        $chain->setTtl(2);

        $chain->set($val2, ...$key2);

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

    public function testHandlerReturnsNullOnDelOfNonExistantItem()
    {
        $chain = $this->chain;

        static::assertFalse($chain->del(['this', 'doesnt', 'exist']));
    }

    public function tearDown()
    {
        if ($this->chain instanceof AbstractHandlerChain) {
            $this->chain->flushAll();
        }

        if ($this->chain instanceof AbstractHandlerChain) {
            $gen = $keyPrefix = $this->chain->getActiveHandler()->getKeyGenerator();
            if ($gen instanceof KeyGenerator) {
                $keyPrefix = $gen->getKeyPrefix();
                try {
                    $keyPrefixEntity = $this->container->get('s.cache.cache_db_handler_prefix.repo')->findOneBySlug($keyPrefix);
                    if ($keyPrefixEntity) {
                        $em = $this->container->get('doctrine.orm.entity_manager');
                        $em->remove($keyPrefixEntity);
                        $em->flush();
                    }
                } catch (NoResultException $e) {
                    // do nothing
                }
            }
        }

        parent::tearDown();
    }
}

/* EOF */
