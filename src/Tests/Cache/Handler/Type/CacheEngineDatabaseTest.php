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

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Faker\Factory;
use Faker\Generator;
use Faker\ORM\Doctrine\Populator;
use Scribe\Utility\Serializer\Serializer;
use Scribe\Utility\UnitTest\AbstractMantleKernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineDatabase;
use Scribe\CacheBundle\Cache\Handler\Chain\AbstractCacheChain;
use Scribe\CacheBundle\KeyGenerator\KeyGenerator;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorInterface;

/**
 * Class CacheEngineDatabaseTest.
 */
class CacheEngineDatabaseTest extends AbstractMantleKernelTestCase
{
    const FULLY_QUALIFIED_CLASS_NAME = 'Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineDatabase';

    /**
     * @var AbstractCacheChain
     */
    public $chain;

    /**
     * @var \Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineDatabase
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

        $this->chain = $this->container->get('s.cache.chain');
        $this->chain->reDetermineActiveHandler('database');
        $this->type = $this->chain->getActiveHandler();
    }

    /**
     * @return EntityManager
     */
    public function getEm()
    {
        return $this->container->get('doctrine.orm.default_entity_manager');
    }

    /**
     * getNewHandlerType.
     *
     * @return \Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineDatabase
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
     * @return \Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineDatabase
     */
    public function getNewHandlerTypeEmpty(KeyGeneratorInterface $keyGenerator = null, $ttl = 1800, $priority = null, $disabled = false, callable $supportedDecider = null)
    {
        return new \Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineDatabase($keyGenerator, $ttl, $priority, $disabled, $supportedDecider);
    }

    /**
     * getNewHandlerTypeNotSupported.
     *
     * @param KeyGeneratorInterface $keyGenerator
     * @param int                   $ttl
     * @param null                  $priority
     * @param bool                  $disabled
     *
     * @return \Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineDatabase
     */
    public function getNewHandlerTypeNotSupported(KeyGeneratorInterface $keyGenerator = null, $ttl = 1800, $priority = null, $disabled = false)
    {
        $supportedDecider = function () { return false; };

        return $this->getNewHandlerTypeEmpty(new KeyGenerator(), 1800, 1, false, $supportedDecider);
    }

    /**
     * @group CacheEngine
     * @group CacheEngineDatabase
     */
    public function testHandlerInNotSupportedState()
    {
        $handler = $this->getNewHandlerTypeEmpty(
            static::$staticContainer->get('s.cache.key_generator'),
            10, 1, false, function() { return false; }
        );

        $handler->setManagerAndRepositories(
            static::$staticContainer->get('doctrine.orm.default_entity_manager'),
            static::$staticContainer->get('s.cache.engine_database_item.repo'),
            static::$staticContainer->get('s.cache.engine_database_prefix.repo')
        );

        static::assertFalse($handler->flushStaleItems());
        static::assertFalse($handler->set('data', 'key'));
        static::assertFalse($handler->has('foo', 'bar'));
        static::assertFalse($handler->del('bar', 'foo'));
        static::assertFalse($handler->flushAll());
    }

    /**
     * @group CacheEngine
     * @group CacheEngineDatabase
     */
    public function testHandlerInDisabledStateWithCustomDecider()
    {
        $handler = $this->getNewHandlerTypeEmpty(
            static::$staticContainer->get('s.cache.key_generator'),
            10, 1, true, function() { return false; }
        );

        $handler->setManagerAndRepositories(
            static::$staticContainer->get('doctrine.orm.default_entity_manager'),
            static::$staticContainer->get('s.cache.engine_database_item.repo'),
            static::$staticContainer->get('s.cache.engine_database_prefix.repo')
        );

        static::assertFalse($handler->isSupported());
        static::assertFalse($handler->flushStaleItems());
        static::assertFalse($handler->set('data', 'key'));
        static::assertFalse($handler->has('foo', 'bar'));
        static::assertFalse($handler->del('bar', 'foo'));
        static::assertFalse($handler->flushAll());
    }

    /**
     * @group CacheEngine
     * @group CacheEngineDatabase
     */
    public function testHandlerInDisabledStateWithDefaultDecider()
    {
        $handler = $this->getNewHandlerTypeEmpty(
            static::$staticContainer->get('s.cache.key_generator'),
            10, 1, true, null
        );

        $handler->setManagerAndRepositories(
            static::$staticContainer->get('doctrine.orm.default_entity_manager'),
            static::$staticContainer->get('s.cache.engine_database_item.repo'),
            static::$staticContainer->get('s.cache.engine_database_prefix.repo')
        );

        static::assertFalse($handler->isSupported());
        static::assertFalse($handler->flushStaleItems());
        static::assertFalse($handler->set('data', 'key'));
        static::assertFalse($handler->has('foo', 'bar'));
        static::assertFalse($handler->del('bar', 'foo'));
        static::assertFalse($handler->flushAll());
    }

    /**
     * @group CacheEngine
     * @group CacheEngineDatabase
     */
    public function testGetWithoutKeyExceptionHandling()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\InvalidArgumentException',
            '#Cannot attempt to get a cached value without setting a key to retrieve it in .*#'
        );

        $this->type->get();
    }

    /**
     * @group CacheEngine
     * @group CacheEngineDatabase
     */
    public function testToStringFQN()
    {
        static::assertEquals(self::FULLY_QUALIFIED_CLASS_NAME, (string) $this->type);
        static::assertEquals(self::FULLY_QUALIFIED_CLASS_NAME, $this->type->__toString());
    }

    /**
     * @group CacheEngine
     * @group CacheEngineDatabase
     */
    public function testGetType()
    {
        static::assertEquals('database', $this->type->getType());
        static::assertEquals(
            self::FULLY_QUALIFIED_CLASS_NAME,
            $this->type->getType(true)
        );
    }

    /**
     * @group CacheEngine
     * @group CacheEngineDatabase
     */
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
            $type = clone $this->type;
            $type->setManagerAndRepositories(
                static::$staticContainer->get('doctrine.orm.default_entity_manager'),
                static::$staticContainer->get('s.cache.engine_database_item.repo'),
                static::$staticContainer->get('s.cache.engine_database_prefix.repo')
            );
            static::assertNull($type->get(['random', 'key']));
        }

        static::assertNotEquals($val1, $chain->get(...$key1));
        static::assertNotEquals($val2, $chain->get(...$key2));
        static::assertNotEquals($val3, $chain->get(...$key3));

        $chain->setTtlToDefault();
    }

    /**
     * @group CacheEngine
     * @group CacheEngineDatabase
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
     * @group CacheEngine
     * @group CacheEngineDatabase
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
     * @group CacheEngine
     * @group CacheEngineDatabase
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
     * @group CacheEngine
     * @group CacheEngineDatabase
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
     * @group CacheEngine
     * @group CacheEngineDatabase
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
     * @group CacheEngine
     * @group CacheEngineDatabase
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
     * @group CacheEngine
     * @group CacheEngineDatabase
     */
    public function testIsNotSupported()
    {
        static::assertTrue($this->type->isSupported());

        $decider = function () { return (bool) false; };
        $this->type->setSupportedDecider($decider);

        static::assertFalse($this->type->isSupported());
    }

    /**
     * @group CacheEngine
     * @group CacheEngineDatabase
     */
    public function testIsNotSupportedInternalCalls()
    {
        $decider = function () { return (bool) false; };
        $this->type->setSupportedDecider($decider);

        static::assertFalse($this->type->isSupported());

        $this->type->__construct(
            new KeyGenerator()
        );
        $this->type->setManagerAndRepositories(
            $this->container->get('doctrine.orm.entity_manager'),
            $this->container->get('s.cache.engine_database_item.repo'),
            $this->container->get('s.cache.engine_database_prefix.repo')
        );

        $this->type->clearSupportedDecider();

        static::assertTrue($this->type->isSupported());
    }

    /**
     * @group CacheEngine
     * @group CacheEngineDatabase
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

    /**
     * @group CacheEngine
     * @group CacheEngineDatabase
     */
    public function testHandlerReturnsNullOnDelOfInvalidItem()
    {
        $chain = $this->chain;

        static::assertFalse($chain->del(['this', 'doesnt', 'exist']));
    }

    /**
     * @group CacheEngine
     * @group CacheEngineDatabase
     * @group Faker
     */
    public function testLotsOfDataWithFaker()
    {
        $em = $this->getEm();

        $prefixRepo = $em->getRepository('Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabasePrefix');
        $itemRepo = $em->getRepository('Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabaseItem');
        $prefixes = $prefixRepo->findAll();
        $items = $itemRepo->findAll();

        foreach ($items as $i) {
            $em->remove($i);
            $em->flush();
            $em->clear($i);
        }

        foreach ($prefixes as $p) {
            $em->remove($p);
            $em->flush();
            $em->clear($p);
        }

        $keyGenerator = new KeyGenerator();

        $faker = Factory::create();

        $slugger = function() use ($faker) { return 'slug_'.$faker->randomNumber(6); };

        $populator = new Populator($faker, $em);
        $populator->addEntity('Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabasePrefix', 10, [
            'slug' => $slugger
        ]);
        $populator->execute($em);

        $prefixes = $prefixRepo->findAll();
        $prefixIds = [];

        static::assertCount(10, $prefixes);

        foreach ($prefixes as $p) {
            static::assertNotNull($p->getSlug());
            static::assertNotNull($p->getId());

            $prefixIds[] = $p->getId();
        }

        $populator->addEntity('Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabaseItem', 1000, [
            'k' => function() use ($faker, $keyGenerator) { return $keyGenerator->getKey($faker->paragraph(1), $faker->paragraph(1)); },
            'value' => function() use ($faker) { return $faker->sentence(50); },
            'slug' => $slugger,
            'prefix' => function() use ($faker, $prefixes) { return $faker->randomElement($prefixes); }
        ]);

        $populator->execute($em);

        $items = $itemRepo->findAll();

        static::assertCount(1000, $items);

        foreach ($items as $i) {
            static::assertNotNull($i->getSlug());
            static::assertNotNull($i->getK());
            static::assertNotNull($i->getValue());
            static::assertNotNull($i->getTtl());
            static::assertNotNull($i->getPrefix());
            static::assertTrue(gettype($i->getSlug()) === 'string');
            static::assertTrue(gettype($i->getK()) === 'string');
            static::assertTrue(gettype($i->getValue()) === 'string');
            static::assertTrue(gettype($i->getTtl()) === 'integer');
            static::assertInstanceOf('Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabasePrefix', $i->getPrefix());
            static::assertTrue(in_array($i->getPrefix()->getId(), $prefixIds, true));

            $em->remove($i);
        }

        $em->flush();
        $em->clear('Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabaseItem');

        foreach ($prefixes as $p) {
            $em->remove($p);
        }

        $em->flush();
        $em->clear('Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabasePrefix');
    }

    public function tearDown()
    {
        $em = $this->getEm();

        $prefixRepo = $em->getRepository('Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabasePrefix');
        $itemRepo = $em->getRepository('Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabaseItem');
        $prefixes = $prefixRepo->findAll();
        $items = $itemRepo->findAll();

        foreach ($items as $i) {
            $em->remove($i);
        }

        $em->flush();
        $em->clear('Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabaseItem');

        foreach ($prefixes as $p) {
            $em->remove($p);
        }

        $em->flush();
        $em->clear('Scribe\CacheBundle\Doctrine\Entity\Cache\CacheEngineDatabasePrefix');

        if ($this->chain instanceof AbstractCacheChain) {
            $gen = $keyPrefix = $this->chain->getActiveHandler()->getKeyGenerator();
            if ($gen instanceof KeyGenerator) {
                $keyPrefix = $gen->getKeyPrefix();
                try {
                    $keyPrefixEntity = $this->container->get('s.cache.engine_database_prefix.repo')->findOneBySlug($keyPrefix);
                    if ($keyPrefixEntity) {
                        $em = $this->container->get('doctrine.orm.entity_manager');
                        $em->remove($keyPrefixEntity);
                        $em->flush();
                    }
                } catch (\Exception $e) {
                    // do nothing
                }
            }
        }

        parent::tearDown();
    }
}

/* EOF */
