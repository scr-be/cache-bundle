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

use Scribe\Utility\UnitTest\AbstractMantleKernelTestCase;
use Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeMemcached;
use Scribe\CacheBundle\Cache\Handler\Chain\AbstractHandlerChain;
use Scribe\CacheBundle\KeyGenerator\KeyGenerator;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class HandlerTypeMemcachedTest.
 *
 *
 * @Title("Memcache Cache Handler Test")
 */
class HandlerTypeMemcachedTest extends AbstractMantleKernelTestCase
{
    const FULLY_QUALIFIED_CLASS_NAME = 'Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeMemcached';

    /**
     * @var AbstractHandlerChain
     */
    public $chain;

    /**
     * @var HandlerTypeMemcached
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
        $handlers = $this->chain->getHandlerCollection();
        $memcachedHandler = null;
        foreach ($handlers as $h) {
            if ($h instanceof HandlerTypeMemcached) {
                $memcachedHandler = $h;
            }
        }
        if (null === $memcachedHandler) {
            throw new \PHPUnit_Framework_Exception('Could not find Memcached Handler');
        }
        $this->chain->setActiveHandler($memcachedHandler);
        $this->type = $this->chain->getActiveHandler();
    }

    /**
     * getNewHandlerType.
     *
     * @return HandlerTypeMemcached
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
     * @return HandlerTypeMemcached
     */
    public function getNewHandlerTypeEmpty(KeyGeneratorInterface $keyGenerator = null, $ttl = 1800, $priority = null, $disabled = false, callable $supportedDecider = null)
    {
        return new HandlerTypeMemcached($keyGenerator, $ttl, $priority, $disabled, $supportedDecider);
    }

    /**
     * getNewHandlerTypeNotSupported.
     *
     * @param KeyGeneratorInterface $keyGenerator
     * @param int                   $ttl
     * @param null                  $priority
     * @param bool                  $disabled
     *
     * @return HandlerTypeMemcached
     */
    public function getNewHandlerTypeNotSupported(KeyGeneratorInterface $keyGenerator = null, $ttl = 1800, $priority = null, $disabled = false)
    {
        $supportedDecider = function () { return false; };

        return $this->getNewHandlerTypeEmpty(new KeyGenerator(), 1800, 1, false, $supportedDecider);
    }

    /**
     * @Title("Test Exception is Thrown on Unknown Option Type");
     * @Features({"Option Handling", "DI", "Exception Handling"})
     * @Stories({"Handler should be able to handle configuration"})
     */
    public function testUnknownOptionType()
    {
        $this->setExpectedException(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            'Unknown memcached option type unknown_option_type specified.'
        );

        $this->type->setOptions(['unknown_option_type' => true]);
    }

    /**
     * @Title("Test Exception is Thrown on Unknown Server Configuration");
     * @Features({"Option Handling", "DI", "Exception Handling"})
     * @Stories({"Handler should be able to handle configuration"})
     */
    public function testInvalidServerOption()
    {
        $this->setExpectedException(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            'Unknown number of server connection parameters. Please provide 3: ip/host, port, and weight.'
        );

        $this->type->addServers(['invalid_server_opts' => ['too', 'many', 'args', 'for', 'server', 'config']]);
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
        $this->assertEquals(self::FULLY_QUALIFIED_CLASS_NAME, (string) $this->type);
        $this->assertEquals(self::FULLY_QUALIFIED_CLASS_NAME, $this->type->__toString());
    }

    /**
     * @Title("Attempt to get the non-fully qualified class name for handler")
     * @Features({"To String", "Self-Aware"})
     * @Stories({"Handler should be able to determine what type it is"})
     */
    public function testGetType()
    {
        $this->assertEquals('memcached', $this->type->getType());
        $this->assertEquals(
            self::FULLY_QUALIFIED_CLASS_NAME,
            $this->type->getType(true)
        );
    }

    /**
     * @Title("Confirm the Memcached handler can cache")
     * @Features({"Can Cache"})
     * @Stories({"Handler should be able to set/get/has/del/flush"})
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

        $this->assertEquals($val1, $chain->get(...$key1));
        $this->assertEquals($val2, $chain->get(...$key2));
        $this->assertEquals($val3, $chain->get(...$key3));
    }

    /**
     * @Title("Confirm the Memcached handler can determine if it has a cached item")
     * @Features({"Can Check Cache"})
     * @Stories({"Handler should be able to set/get/has/del/flush"})
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

        $this->assertEquals($val1, $chain->get(...$key1));
        $this->assertEquals($val2, $chain->get(...$key2));
        $this->assertEquals($val3, $chain->get(...$key3));

        $this->assertTrue($chain->has(...$key1));
        $this->assertTrue($chain->has(...$key2));
        $this->assertTrue($chain->has(...$key3));
    }

    /**
     * @Title("Confirm the Memcached handler can flush its cache")
     * @Features({"Can Cache", "Can Flush All"})
     * @Stories({"Handler should be able to set/get/has/del/flush"})
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

        $this->assertEquals($val1, $chain->get(...$key1));
        $this->assertEquals($val2, $chain->get(...$key2));
        $this->assertEquals($val3, $chain->get(...$key3));

        $this->assertTrue($chain->has(...$key1));
        $this->assertTrue($chain->has(...$key2));
        $this->assertTrue($chain->has(...$key3));

        $chain->flushAll();

        $this->assertNull($chain->get(...$key1));
        $this->assertNull($chain->get(...$key2));
        $this->assertNull($chain->get(...$key3));

        $this->assertFalse($chain->has(...$key1));
        $this->assertFalse($chain->has(...$key2));
        $this->assertFalse($chain->has(...$key3));
    }

    /**
     * @Title("Confirm the Memcached handler honors TTL")
     * @Features({"Can Cache", "Can Flush All", "Can Respect TTL"})
     * @Stories({"Handler should be able to set/get/has/del/flush"})
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

        $this->assertEquals($val1, $chain->get(...$key1));
        $this->assertTrue($chain->has(...$key1));
        $this->assertEquals($val2, $chain->get(...$key2));
        $this->assertTrue($chain->has(...$key2));

        sleep(4);

        $this->assertEquals($val1, $chain->get(...$key1));
        $this->assertTrue($chain->has(...$key1));
        $this->assertNotNull($chain->get(...$key1));
        $this->assertNotEquals($val2, $chain->get(...$key2));
        $this->assertFalse($chain->has(...$key2));
        $this->assertNull($chain->get(...$key2));

        $chain->flushAll();

        $this->assertNotEquals($val1, $chain->get(...$key1));
        $this->assertFalse($chain->has(...$key1));
        $this->assertNull($chain->get(...$key1));
        $this->assertNotEquals($val2, $chain->get(...$key2));
        $this->assertFalse($chain->has(...$key2));
        $this->assertNull($chain->get(...$key2));
    }

    /**
     * @Title("Confirm the Memcached handler can delete cached values")
     * @Features({"Can Cache", "Can Flush All", "Can Respect TTL", "Can Delete"})
     * @Stories({"Handler should be able to set/get/has/del/flush"})
     */
    public function testMemcachedHandlerCanCacheAndDelete()
    {
        $val1 = $key1 = [1122, 2233, 3344];
        $val2 = $key2 = [2233, 3455, 4455];

        $chain = $this->chain;
        $chain->setTtl(2);

        $chain->set($val1, ...$key1);
        $chain->set($val2, ...$key2);

        $this->assertEquals($val1, $chain->get(...$key1));
        $this->assertTrue($chain->has(...$key1));

        $this->assertEquals($val2, $chain->get(...$key2));
        $this->assertTrue($chain->has(...$key2));

        $chain->del(...$key1);

        $this->assertNotEquals($val1, $chain->get(...$key1));
        $this->assertFalse($chain->has(...$key1));

        sleep(2);

        $this->assertFalse($chain->has(...$key2));

        $chain->flushAll();

        $this->assertNull($chain->get(...$key1));
        $this->assertNull($chain->get(...$key2));
    }

    /**
     * @Title("Test a collection of possible option values")
     * @Features({"Option Handling", "DI"})
     * @Stories({"Handler should be able to handle configuration"})
     */
    public function testOptions()
    {
        $this->assertEquals(\Memcached::SERIALIZER_IGBINARY, $this->type->getOption(\Memcached::OPT_SERIALIZER));

        $opts = [
            'serializer' => 'json',
            'compression_method' => 'zlib',
        ];

        $this->type->setOptions($opts);

        $this->assertEquals(\Memcached::SERIALIZER_JSON, $this->type->getOption(\Memcached::OPT_SERIALIZER));

        $opts = [
            'serializer' => 'php',
        ];

        $this->type->setOptions($opts);

        $this->assertEquals(\Memcached::SERIALIZER_PHP, $this->type->getOption(\Memcached::OPT_SERIALIZER));
    }

    /**
     * @Title("Check isSupported closure handler")
     * @Features({"Option Handling"})
     * @Stories({"Handler should be able to determine supported state based on passed closure"})
     */
    public function testIsNotSupported()
    {
        $this->assertTrue($this->type->isSupported());

        $decider = function () { return (bool) false; };
        $this->type->setSupportedDecider($decider);

        $this->assertFalse($this->type->isSupported());
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

        $this->assertFalse($this->type->isSupported());

        $this->type->__construct();
        $this->type->setOptions([]);
        $this->type->setServers([]);

        $this->type->unsetSupportedDecider();

        $this->assertTrue($this->type->isSupported());
    }

    /**
     * @Title("Handler can have TTL values changed randomly and continue to operate properly")
     * @Features({"Can Cache", "Can Respect TTL"})
     * @Stories({"Handler should be able to set/get/has/del/flush"})
     */
    public function testMemcachedHandlerCanChangeTtl()
    {
        $chain = $this->chain;
        $chain->setTtl(8);

        $this->assertEquals(8, $chain->getTtl());

        $val1 = $key1 = [1, 2, 3];
        $val2 = $key2 = [2, 3, 4];

        $chain->set($val1, ...$key1);

        $chain->setTtl(2);

        $chain->set($val2, ...$key2);

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
        if ($this->chain instanceof AbstractHandlerChain) {
            $this->chain->flushAll();
        }

        parent::tearDown();
    }
}

/* EOF */
