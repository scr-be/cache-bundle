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

namespace Scribe\Teavee\ObjectCacheBundle\Tests\Component\Cache\Redis;

use Scribe\Teavee\ObjectCacheBundle\Component\Cache\Redis\RedisAttendant;
use Scribe\WonkaBundle\Utility\TestCase\KernelTestCase;
use Scribe\Wonka\Utility\Serializer\Serializer;
use Scribe\Teavee\ObjectCacheBundle\Component\Generator\KeyGenerator;

/**
 * Class RedisAttendantTest.
 */
class RedisAttendantTest extends KernelTestCase
{
    /**
     * @var RedisAttendant
     */
    public static $r;

    public function setUp()
    {
        parent::setUp();

        self::$r = self::$staticContainer->get('s.teavee_object_cache.attendant_redis');
    }

    public function tearDown()
    {
        try {
            if (method_exists(self::$r, 'flush')) {
                self::$r->flush();
            }
        } catch (\Exception $e) {
            // do nothing
        }

        parent::tearDown();
    }

    public function testInterface()
    {
        self::assertInstanceOf('Scribe\\WonkaBundle\\Component\\DependencyInjection\\Compiler\\Attendant\\AbstractCompilerAttendant', self::$r);
        self::assertInstanceOf('Scribe\\Teavee\\ObjectCacheBundle\\Component\\Cache\\CacheAttendantInterface', self::$r);
        self::assertInstanceOf('Scribe\\Teavee\\ObjectCacheBundle\\Component\\Cache\\Redis\\RedisAttendantInterface', self::$r);
    }

    public function testOptionsDefault()
    {
        $keys = ['serializer'];
        $values = ['serializer_php'];

        foreach ($keys as $i => $k) {
            self::assertArrayHasKey($k, self::$r->getOptions());
            self::assertEquals($values[$i], self::$r->getOptions()[$k]);
        }
    }

    public function testOptionsInvalid()
    {
        $options = self::$r->getOptions();
        $options['unknown_options'] = true;

        self::setExpectedException('Scribe\\Wonka\\Exception\\InvalidArgumentException');
        self::$r->setOptions($options);
        self::assertEquals($options, self::$r->getOptions());
        self::$r->set('dats', 'value');
    }

    public function testServersInvalid()
    {
        $r = self::$r;
        $initialized = (new \ReflectionClass($r))
            ->getProperty('initialized');
        $initialized->setAccessible(true);
        $initialized->setValue($r, false);

        $config['host'] = '10.99.99.99';

        self::setExpectedException('Scribe\\Wonka\\Exception\\InvalidArgumentException');
        self::$r->setServer($config);
        self::assertEquals($config, $r->getServer());
        self::$r->set('v', 'k');
    }

    public function testIsSupported()
    {
        self::assertTrue(self::$r->isSupported());
    }

    public function testListKeys()
    {
        $set = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];

        self::assertEmpty(self::$r->listKeys());

        $i = 0;
        foreach ($set as $key => $value) {
            self::assertCount($i, self::$r->listKeys());
            self::$r->set($value, $key);
            $i++;
            self::assertCount($i, self::$r->listKeys());
            self::assertNotEmpty(self::$r->listKeys());
        }
    }

    public function testBasicCaching()
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
            self::assertFalse(self::$r->has(...$keysSet[$i]));
            self::assertFalse(self::$r->del(...$keysSet[$i]));
            self::assertNull(self::$r->get(...$keysSet[$i]));
            self::assertTrue(self::$r->set($dataSet[$i], ...$keysSet[$i]));
            self::assertTrue(self::$r->has(...$keysSet[$i]));
            self::assertEquals($dataSet[$i], self::$r->get());
            self::assertEquals($dataSet[$i], self::$r->get(...$keysSet[$i]));
            self::assertTrue(self::$r->del(...$keysSet[$i]));
            self::assertTrue(self::$r->set($dataSet[$i], ...$keysSet[$i]));

            self::$r->setKey(...$keysSet[$i]);
            self::assertTrue(self::$r->del());
            self::assertFalse(self::$r->has());
            self::assertNull(self::$r->get());
            self::assertTrue(self::$r->set($dataSet[$i]));
            self::assertTrue(self::$r->has());
            self::assertEquals($dataSet[$i], self::$r->get());
            self::assertTrue(self::$r->has(...$keysSet[$i]));
            self::assertEquals($dataSet[$i], self::$r->get(...$keysSet[$i]));
            self::assertTrue(self::$r->del());
            self::assertFalse(self::$r->del());
            self::assertFalse(self::$r->has());
            self::assertNull(self::$r->get());
        }
    }

    public function testTtlCaching()
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
            self::$r->resetTtl();
            self::$r->setTtl($ttlsSet[$i]);
            self::assertFalse(self::$r->has(...$keysSet[$i]));
            self::assertFalse(self::$r->del(...$keysSet[$i]));
            self::assertNull(self::$r->get(...$keysSet[$i]));
            self::assertTrue(self::$r->set($dataSet[$i], ...$keysSet[$i]));
            self::assertTrue(self::$r->has(...$keysSet[$i]));
            self::assertEquals($dataSet[$i], self::$r->get());
            self::assertEquals($dataSet[$i], self::$r->get(...$keysSet[$i]));
            self::assertTrue(self::$r->del(...$keysSet[$i]));
            self::assertTrue(self::$r->set($dataSet[$i], ...$keysSet[$i]));

            self::$r->setKey(...$keysSet[$i]);
            self::assertTrue(self::$r->del());
            self::assertFalse(self::$r->has());
            self::assertNull(self::$r->get());
            self::assertTrue(self::$r->set($dataSet[$i]));
            self::assertTrue(self::$r->has());
            self::assertEquals($dataSet[$i], self::$r->get());
            self::assertTrue(self::$r->has(...$keysSet[$i]));
            self::assertEquals($dataSet[$i], self::$r->get(...$keysSet[$i]));
        }

        foreach (range(0, count($dataSet) - 1) as $i) {
            sleep($waitSet[$i] - 2);
            self::$r->setKey(...$keysSet[$i]);
            self::assertTrue(self::$r->has());
            self::assertEquals($dataSet[$i], self::$r->get());
            sleep(2);
            self::assertFalse(self::$r->has());
            self::assertNotEquals($dataSet[$i], self::$r->get());
        }
    }
}

/* EOF */
