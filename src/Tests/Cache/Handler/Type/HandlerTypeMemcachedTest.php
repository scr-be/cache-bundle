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
use Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeMemcached;
use Scribe\CacheBundle\Cache\Handler\Chain\AbstractHandlerChain;
use Scribe\CacheBundle\KeyGenerator\KeyGenerator;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class HandlerTypeMemcachedTest
 *
 * @package Scribe\CacheBundle\Tests\Cache\Handler\Type
 */
class HandlerTypeMemcachedTest extends PHPUnit_Framework_TestCase
{
    const FULLY_QUALIFIED_CLASS_NAME = 'Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeMemcached';

    /**
     * @var AbstractHandlerChain
     */
    protected $chain;

    /**
     * @var HandlerTypeMemcached
     */
    protected $type;

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function setUp()
    {
        $kernel = new \AppKernel('test', true);
        $kernel->boot();

        $this->container = $kernel->getContainer();
        $this->chain     = $this->container->get('scribe_cache.handler_chain');
        $this->type      = $this->chain->getActiveHandler();
    }

    protected function getNewHandlerType()
    {
        return $this->getNewHandlerTypeEmpty(new KeyGenerator);
    }

    protected function getNewHandlerTypeEmpty(KeyGeneratorInterface $keyGenerator = null, $ttl = 1800, $priority = null, $disabled = false, callable $supportedDecider = null)
    {
        return new HandlerTypeMemcached($keyGenerator, $ttl, $priority, $disabled, $supportedDecider);
    }

    protected function getNewHandlerTypeNotSupported(KeyGeneratorInterface $keyGenerator = null, $ttl = 1800, $priority = null, $disabled = false)
    {
        $supportedDecider = function() { return false; };

        return $this->getNewHandlerTypeEmpty(new KeyGenerator, 1800, 1, false, $supportedDecider);
    }

    /**
     * @expectedException        Scribe\CacheBundle\Exceptions\RuntimeException
     * @expectedExceptionMessage Unknown memcached option type unknown_option_type specified.
     */
    public function testUnknownOptionType()
    {
        $this->type->setOptions(['unknown_option_type' => true]);
    }

    /**
     * @expectedException        Scribe\CacheBundle\Exceptions\RuntimeException
     * @expectedExceptionMessage Unknown number of server connection parameters. Please provide 3: ip/host, port, and weight.
     */
    public function testInvalidServerOption()
    {
        $this->type->addServers(['invalid_server_opts' => ['too', 'many', 'args', 'for', 'server', 'config']]);
    }

    /**
     * @expectedException        Scribe\CacheBundle\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Cannot attempt to get a cached value without setting a key to retrieve it.
     */
    public function testGetWithoutKeyExceptionHandling()
    {
        $this
            ->type
            ->get()
        ;
    }

    public function testToString()
    {
        $this->assertEquals(self::FULLY_QUALIFIED_CLASS_NAME, (string) $this->type);
        $this->assertEquals(self::FULLY_QUALIFIED_CLASS_NAME, $this->type->__toString());
    }

    public function testGetType()
    {
        $this->assertEquals('memcached', $this->type->getType());
        $this->assertEquals(
            self::FULLY_QUALIFIED_CLASS_NAME,
            $this->type->getType(true)
        );
    }

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

        $chain->flushAll();

        $this->assertNull($chain->get(...$key1));
        $this->assertNull($chain->get(...$key2));
        $this->assertNull($chain->get(...$key3));
    }

    public function testMemcachedHandlerCanCacheWithValidateTtl()
    {
        $chain = $this->chain;
        $chain->getActiveHandler()->setTtl(1);

        $val1 = $key1 = [1, 2, 3];

        $chain->set($val1, ...$key1);

        $this->assertEquals($val1, $chain->get(...$key1));
        $this->assertTrue($chain->has(...$key1));

        sleep(1);

        $this->assertFalse($chain->has(...$key1));

        $chain->flushAll();

        $this->assertNull($chain->get(...$key1));
    }

    public function testMemcachedHandlerCanCacheAndDelete()
    {
        $chain = $this->chain;
        $chain->getActiveHandler()->setTtl(2);

        $val1 = $key1 = [1, 2, 3];
        $val2 = $key2 = [2, 3, 4];

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

    public function testOptions()
    {
        $opts = [
            'serializer' => 'json',
            'compression_method' => 'zlib'
        ];

        $this->type->setOptions($opts);

        $opts = [
            'serializer' => 'php'
        ];

        $this->type->setOptions($opts);
    }

    public function testIsNotSupported()
    {
        $this->assertTrue($this->type->isSupported());

        $decider = function() { return (bool) false; };
        $this->type->setSupportedDecider($decider);

        $this->assertFalse($this->type->isSupported());
    }

    public function testIsNotSupportedInternalCalls()
    {
        $decider = function() { return (bool) false; };
        $this->type->setSupportedDecider($decider);

        $this->assertFalse($this->type->isSupported());

        $this->type->__construct();
        $this->type->setOptions([]);
        $this->type->setServers([]);

        $this->type->unsetSupportedDecider();

        $this->assertTrue($this->type->isSupported());
    }

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

    protected function tearDown()
    {
        if ($this->chain instanceof AbstractHandlerChain) {
            $this->chain->flushAll();
        }
    }
}

/* EOF */
