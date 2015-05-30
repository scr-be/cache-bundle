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

use Scribe\CacheBundle\Cache\Handler\Engine\AbstractCacheEngine;
use Scribe\Utility\UnitTest\AbstractMantleKernelTestCase;
use Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineMemcached;
use Scribe\CacheBundle\Cache\Handler\Chain\AbstractCacheChain;
use Scribe\CacheBundle\KeyGenerator\KeyGenerator;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CacheEngineMemcachedTest.
 *
 *
 * @Title("Memcache Cache Handler Test")
 */
class CacheEngineMemcachedTest extends AbstractMantleKernelTestCase
{
    const FULLY_QUALIFIED_CLASS_NAME = 'Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineMemcached';

    /**
     * @var AbstractCacheChain
     */
    public $chain;

    /**
     * @var CacheEngineMemcached
     */
    public $type;

    /**
     * @var CacheEngineMemcached
     */
    public $typeClean;

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
        $handlers = $this->chain->getHandlerCollection();
        $memcachedHandler = null;
        foreach ($handlers as $h) {
            if ($h instanceof CacheEngineMemcached) {
                $memcachedHandler = $h;
            }
        }
        if (null === $memcachedHandler) {
            throw new \PHPUnit_Framework_Exception('Could not find Memcached Handler');
        }
        $this->typeClean = clone $memcachedHandler;
        $this->chain->setActiveHandler($memcachedHandler);
        $this->type = $this->chain->getActiveHandler();
    }

    /**
     * getNewHandlerType.
     *
     * @return CacheEngineMemcached
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
     * @return CacheEngineMemcached
     */
    public function getNewHandlerTypeEmpty(KeyGeneratorInterface $keyGenerator = null, $ttl = 1800, $priority = null, $disabled = false, callable $supportedDecider = null)
    {
        return (new CacheEngineMemcached($keyGenerator, $ttl, $priority, $disabled, $supportedDecider))->setOptions([])->setServers([]);
    }

    /**
     * getNewHandlerTypeNotSupported.
     *
     * @param KeyGeneratorInterface $keyGenerator
     * @param int                   $ttl
     * @param null                  $priority
     * @param bool                  $disabled
     *
     * @return CacheEngineMemcached
     */
    public function getNewHandlerTypeNotSupported(KeyGeneratorInterface $keyGenerator = null, $ttl = 1800, $priority = null, $disabled = false)
    {
        $supportedDecider = function () { return false; };

        return $this->getNewHandlerTypeEmpty(new KeyGenerator(), 1800, 1, false, $supportedDecider)->setOptions([])->setServers([]);
    }

    /**
     * @group CacheEngine
     * @group CacheEngineMemcached
     */
    public function testInitOnGet()
    {
        $type = clone $this->typeClean;
        $type->get('something');
        static::assertTrue($type->isInitialized());
    }

    /**
     * @group CacheEngine
     * @group CacheEngineMemcached
     */
    public function testInitOnGetAndDisabled()
    {
        $type = clone $this->typeClean;
        $type->setEnabled(false);
        $type->get('something');
        static::assertFalse($type->isInitialized());
    }

    /**
     * @group CacheEngine
     * @group CacheEngineMemcached
     */
    public function testGetOptionOnNonInitialized()
    {
        $type = clone $this->typeClean;
        $type->getOption(\Memcached::COMPRESSION_ZLIB);
        static::assertTrue($type->isInitialized());
    }

    /**
     * @group CacheEngine
     * @group CacheEngineMemcached
     */
    public function testGetOptionOnNonInitializedAndDisabled()
    {
        $type = clone $this->typeClean;
        $type->setEnabled(false);
        $type->getOption(\Memcached::COMPRESSION_ZLIB);
        static::assertFalse($type->isInitialized());
    }

    /**
     * @group CacheEngine
     * @group CacheEngineMemcached
     */
    public function testUnknownOptionType()
    {
        $this->type->setOptions(['unknownOptionType' => true]);

        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            '#Unknown memcached option type unknownOptionType specified.*#'
        );

        $this->type->get('call-anything-mutator-to-trigger-lazy-initialization');
    }

    /**
     * @group CacheEngine
     * @group CacheEngineMemcached
     */
    public function testInvalidServerOption()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            '#Unknown number of server connection parameters. Please provide 3: ip/host, port, and weight in .*#'
        );

        $this->type->addServers(['invalid_server_opts' => ['too', 'many', 'args', 'for', 'server', 'config']]);
        $this->type->get('call-anything-mutator-to-trigger-lazy-initialization');
    }

    /**
     * @group CacheEngine
     * @group CacheEngineMemcached
     */
    public function testGetWithoutKeyExceptionHandling()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\InvalidArgumentException',
            '#Cannot attempt to get a cached value without setting a key to retrieve it .*#'
        );

        $this->type->get();
    }

    /**
     * @group CacheEngine
     * @group CacheEngineMemcached
     */
    public function testToStringFQN()
    {
        static::assertEquals(self::FULLY_QUALIFIED_CLASS_NAME, (string) $this->type);
        static::assertEquals(self::FULLY_QUALIFIED_CLASS_NAME, $this->type->__toString());
    }

    /**
     * @group CacheEngine
     * @group CacheEngineMemcached
     */
    public function testGetType()
    {
        static::assertEquals('memcached', $this->type->getType());
        static::assertEquals(
            self::FULLY_QUALIFIED_CLASS_NAME,
            $this->type->getType(true)
        );
    }

    /**
     * @group CacheEngine
     * @group CacheEngineMemcached
     */
    public function testMemcachedHandlerCanCacheAndFlushAll()
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
     * @group CacheEngineMemcached
     */
    public function testMemcachedHandlerCanCacheAndCheck()
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
     * @group CacheEngineMemcached
     */
    public function testMemcacheHandlerCanFlushAll()
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
     * @group CacheEngineMemcached
     */
    public function testMemcachedHandlerCanCacheWithValidateTtl()
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
     * @group CacheEngineMemcached
     */
    public function testMemcachedHandlerCanCacheAndDelete()
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
     * @group CacheEngineMemcached
     */
    public function testOptions()
    {
        static::assertEquals(\Memcached::SERIALIZER_IGBINARY, $this->type->getOption(\Memcached::OPT_SERIALIZER));

        $opts = [
            'serializer' => 'json',
            'compression_method' => 'zlib',
        ];

        $this->type->setOptions($opts);
        $this->type->set('call-any-mutator-to-trigger-lazy-init', 'with', 'some', 'key', 'vals');

        static::assertEquals(\Memcached::SERIALIZER_JSON, $this->type->getOption(\Memcached::OPT_SERIALIZER));

        $opts = [
            'serializer' => 'php',
        ];

        $this->type->setOptions($opts);
        $this->type->set('call-any-mutator-to-trigger-lazy-init', 'with', 'some', 'key', 'vals');

        static::assertEquals(\Memcached::SERIALIZER_PHP, $this->type->getOption(\Memcached::OPT_SERIALIZER));
    }

    /**
     * @group CacheEngine
     * @group CacheEngineMemcached
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
     * @group CacheEngineMemcached
     */
    public function testIsNotSupportedInternalCalls()
    {
        $decider = function () { return (bool) false; };
        $this->type->setSupportedDecider($decider);

        static::assertFalse($this->type->isSupported());

        $this->type->__construct();
        $this->type->setOptions([]);
        $this->type->setServers([]);

        $this->type->clearSupportedDecider();

        static::assertTrue($this->type->isSupported());
    }

    /**
     * @group CacheEngine
     * @group CacheEngineMemcached
     */
    public function testMemcachedHandlerCanChangeTtl()
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

    public function tearDown()
    {
        if ($this->chain instanceof AbstractCacheChain &&
            $this->chain->getActiveHandler() instanceof \Scribe\CacheBundle\Cache\Handler\Engine\AbstractCacheEngine &&
            $this->chain->getActiveHandler()->isEnabled() === true &&
            $this->chain->getActiveHandler()->isSupported() === true)
        {
            try {
                $this->chain->flushAll();
            } catch(\Exception $e) {
                // No need to do anything special...some tests may not allow for a flush when they complete.
            }
        }

        parent::tearDown();
    }
}

/* EOF */
