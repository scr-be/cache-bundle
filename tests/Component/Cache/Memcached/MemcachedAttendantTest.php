<?php

/*
 * This file is part of the Teavee Object Caching Bundle.
 *
 * (c) Scribe Inc.     <oss@scr.be>
 * (c) Rob Frawley 2nd <rmf@scr.be>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\Teavee\ObjectCacheBundle\Tests\Component\Generator\KeyGenerator;

use Scribe\WonkaBundle\Utility\TestCase\KernelTestCase;
use Scribe\Wonka\Utility\Serializer\Serializer;
use Scribe\Teavee\ObjectCacheBundle\Component\Generator\KeyGenerator;
use Scribe\Teavee\ObjectCacheBundle\Component\Cache\Memcached\MemcachedAttendant;

/**
 * Class MemcachedAttendantTest.
 */
class MemcachedAttendantTest extends KernelTestCase
{
    /**
     * @var MemcachedAttendant
     */
    public static $m;

    public function setUp()
    {
        parent::setUp();

        self::$m = self::$staticContainer->get('s.object_cache.attendant_memcached');
    }

    public function tearDown()
    {
        try {
            if (method_exists(self::$m, 'flush')) {
                self::$m->flush();
            }
        } catch (\Exception $e) {
            // do nothing
        }

        parent::tearDown();
    }

    public function test_interface()
    {
        self::assertInstanceOf('Scribe\\WonkaBundle\\Component\\DependencyInjection\\Compiler\\Attendant\\AbstractCompilerAttendant', self::$m);
        self::assertInstanceOf('Scribe\\Teavee\\ObjectCacheBundle\\Component\\Cache\\CacheAttendantInterface', self::$m);
        self::assertInstanceOf('Scribe\\Teavee\\ObjectCacheBundle\\Component\\Cache\\Memcached\\MemcachedAttendantInterface', self::$m);
    }

    public function test_options_default()
    {
        $keys = ['serializer', 'libketama_compatible', 'no_block', 'tcp_nodelay', 'compression', 'compression_type'];
        $values = ['serializer_php', false, true, true, false, 'compression_fastlz'];

        foreach ($keys as $i => $k) {
            self::assertArrayHasKey($k, self::$m->getOptions());
            self::assertEquals($values[$i], self::$m->getOptions()[$k]);
        }
    }

    public function test_servers_default()
    {
        $svrs = ['default'];
        $keys = ['host', 'port', 'weight'];

        foreach ($svrs as $s) {
            self::assertArrayHasKey($s, self::$m->getServers());
            $s = self::$m->getServers()[$s];
            foreach ($keys as $k) {
                self::assertArrayHasKey($k, $s);
            }
        }
    }

    public function test_options_invalid()
    {
        $options = self::$m->getOptions();
        $options['unknown_options'] = true;

        self::setExpectedException('Scribe\\Wonka\\Exception\\InvalidArgumentException');
        self::$m->setOptions($options);
        self::assertEquals($options, self::$m->getOptions());
        self::$m->set('dats', 'value');
    }

    public function test_servers_invalid()
    {
        $servers['invalid_server'] = [
            'ip' => '127.0.0.1',
        ];

        self::setExpectedException('Scribe\\Wonka\\Exception\\InvalidArgumentException');
        self::$m->setServers($servers);
        self::assertEquals($servers, self::$m->getServers());
        self::$m->set('dats', 'value');
    }

    public function test_is_supported()
    {
        self::assertTrue(self::$m->isSupported());
    }

    public function test_basic_caching()
    {
        $dataSet = [
            'a string value',
            1000,
            new \DateTime(),
        ];

        $keysSet = [
            ['a', 'collection', 'of', 'string', 'keys'],
            [new KeyGenerator()],
            [100, ['an', 'array', new \DateTime()], 'string-value', 03030303, []],
        ];

        foreach (range(0, count($dataSet) - 1) as $i) {
            self::assertFalse(self::$m->has(...$keysSet[$i]));
            self::assertFalse(self::$m->del(...$keysSet[$i]));
            self::assertNull(self::$m->get(...$keysSet[$i]));
            self::assertTrue(self::$m->set($dataSet[$i], ...$keysSet[$i]));
            self::assertTrue(self::$m->has(...$keysSet[$i]));
            self::assertEquals($dataSet[$i], self::$m->get());
            self::assertEquals($dataSet[$i], self::$m->get(...$keysSet[$i]));
            self::assertTrue(self::$m->del(...$keysSet[$i]));
            self::assertTrue(self::$m->set($dataSet[$i], ...$keysSet[$i]));

            self::$m->setKey(...$keysSet[$i]);
            self::assertTrue(self::$m->del());
            self::assertFalse(self::$m->has());
            self::assertNull(self::$m->get());
            self::assertTrue(self::$m->set($dataSet[$i]));
            self::assertTrue(self::$m->has());
            self::assertEquals($dataSet[$i], self::$m->get());
            self::assertTrue(self::$m->has(...$keysSet[$i]));
            self::assertEquals($dataSet[$i], self::$m->get(...$keysSet[$i]));
            self::assertTrue(self::$m->del());
            self::assertFalse(self::$m->del());
            self::assertFalse(self::$m->has());
            self::assertNull(self::$m->get());
        }
    }

    public function test_ttl_caching()
    {
        $dataSet = [
            'a string value',
            1000,
            new \DateTime(),
        ];

        $keysSet = [
            ['a', 'collection', 'of', 'string', 'keys'],
            [new KeyGenerator()],
            [100, ['an', 'array', new \DateTime()], 'string-value', 03030303, []],
        ];

        $ttlsSet = [4, 10, 16];
        $waitSet = [4, 6, 6];

        foreach (range(0, count($dataSet) - 1) as $i) {
            self::$m->resetTtl();
            self::$m->setTtl($ttlsSet[$i]);
            self::assertFalse(self::$m->has(...$keysSet[$i]));
            self::assertFalse(self::$m->del(...$keysSet[$i]));
            self::assertNull(self::$m->get(...$keysSet[$i]));
            self::assertTrue(self::$m->set($dataSet[$i], ...$keysSet[$i]));
            self::assertTrue(self::$m->has(...$keysSet[$i]));
            self::assertEquals($dataSet[$i], self::$m->get());
            self::assertEquals($dataSet[$i], self::$m->get(...$keysSet[$i]));
            self::assertTrue(self::$m->del(...$keysSet[$i]));
            self::assertTrue(self::$m->set($dataSet[$i], ...$keysSet[$i]));

            self::$m->setKey(...$keysSet[$i]);
            self::assertTrue(self::$m->del());
            self::assertFalse(self::$m->has());
            self::assertNull(self::$m->get());
            self::assertTrue(self::$m->set($dataSet[$i]));
            self::assertTrue(self::$m->has());
            self::assertEquals($dataSet[$i], self::$m->get());
            self::assertTrue(self::$m->has(...$keysSet[$i]));
            self::assertEquals($dataSet[$i], self::$m->get(...$keysSet[$i]));
        }

        foreach (range(0, count($dataSet) - 1) as $i) {
            sleep($waitSet[$i] - 2);
            self::$m->setKey(...$keysSet[$i]);
            self::assertTrue(self::$m->has());
            self::assertEquals($dataSet[$i], self::$m->get());
            sleep(2);
            self::assertFalse(self::$m->has());
            self::assertNotEquals($dataSet[$i], self::$m->get());
        }
    }
}

/* EOF */
