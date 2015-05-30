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

use Faker\Factory;
use Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineFilesystem;
use Scribe\CacheBundle\KeyGenerator\KeyGenerator;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorInterface;
use Scribe\Utility\UnitTest\AbstractMantleKernelTestCase;

/**
 * Class CacheEngineFilesystem.
 */
class CacheEngineFilesystemTest extends AbstractMantleKernelTestCase
{
    const FULLY_QUALIFIED_CLASS_NAME = 'Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineFilesystem';

    /**
     * @var \Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineFilesystem
     */
    public $type;

    /**
     * @var resource
     */
    public $testResource;

    public function setUp()
    {
        parent::setUp();

        $this->type = $this->getNewHandlerType();
        $this->testResource = fopen(__FILE__, 'r');
    }

    /**
     * @return CacheEngineFilesystem
     */
    public function getFilesystemEngineFromContainer()
    {
        return static::$staticContainer->get('s.cache.chain')->reDetermineActiveHandler('filesystem');
    }

    public function getNewHandlerType()
    {
        return $this->getNewHandlerTypeEmpty(new KeyGenerator());
    }

    public function getNewHandlerTypeEmpty(KeyGeneratorInterface $keyGenerator = null, $ttl = 1800, $priority = null, $disabled = false, callable $supportedDecider = null)
    {
        return new \Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineFilesystem($keyGenerator, $ttl, $priority, $disabled, $supportedDecider);
    }

    public function getNewHandlerTypeNotSupported(KeyGeneratorInterface $keyGenerator = null, $ttl = 1800, $priority = null, $disabled = false)
    {
        $supportedDecider = function () { return false; };

        return $this->getNewHandlerTypeEmpty(new KeyGenerator(), 1800, 1, false, $supportedDecider);
    }

    /**
     * @group CacheEngine
     * @group CacheEngineFilesystem
     */
    public function testInitOnGet()
    {
        $type = $this->getNewHandlerType();
        $type->get('something');
        static::assertTrue($type->isInitialized());
    }

    /**
     * @group CacheEngine
     * @group CacheEngineFilesystem
     */
    public function testInitOnGetAndDisabled()
    {
        $type = $this->getNewHandlerType();
        $type->setEnabled(false);
        $type->get('something');
        static::assertTrue($type->isInitialized());
    }

    /**
     * @group CacheEngine
     * @group CacheEngineFilesystem
     */
    public function testGetWithoutKeyExceptionHandling()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\InvalidArgumentException'
        );

        $this
            ->getNewHandlerType()
            ->get()
        ;
    }

    /**
     * @group CacheEngine
     * @group CacheEngineFilesystem
     */
    public function testSetCacheValueAsResourceExceptionHandling()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            '#As a resource cannot be serialized it cannot be passed as a cache value in .*#'
        );

        $this
            ->getNewHandlerType()
            ->setKey('a', 'b', 'c')
            ->set($this->testResource)
        ;
    }

    /**
     * @group CacheEngine
     * @group CacheEngineFilesystem
     */
    public function testToString()
    {
        static::assertEquals(self::FULLY_QUALIFIED_CLASS_NAME, (string) $this->type);
        static::assertEquals(self::FULLY_QUALIFIED_CLASS_NAME, $this->type->__toString());
    }

    /**
     * @group CacheEngine
     * @group CacheEngineFilesystem
     */
    public function testGetType()
    {
        static::assertEquals('filesystem', $this->type->getType());
        static::assertEquals(
            self::FULLY_QUALIFIED_CLASS_NAME,
            $this->type->getType(true)
        );
    }

    /**
     * @group CacheEngine
     * @group CacheEngineFilesystem
     * @group CacheEngineFilesystemFaker
     * @group Faker
     */
    public function testLotsOfDataWithFaker()
    {
        $fileSystemEngine = $this->getFilesystemEngineFromContainer();
        $fileSystemEngine->setTtl(15);

        $fileSystemEngine->flushAll();

        $dataFaker = Factory::create();
        $dataFaked = [];
        $count = 200;

        for ($i = 0; $i < $count; $i++) {
            $dataFaked[$i] = [
                'key' => [$dataFaker->sentence(), $dataFaker->sentence(), $dataFaker->sentence()],
                'val' => $dataFaker->sentence(),
            ];
        }

        foreach ($dataFaked as $data) {
            static::assertFalse($fileSystemEngine->has(...$data['key']));
            static::assertNull($fileSystemEngine->get(...$data['key']));
            static::assertTrue($fileSystemEngine->set($data['val'], ...$data['key']));
            static::assertTrue($fileSystemEngine->has(...$data['key']));
        }

        for ($i = 0; $i < $count; $i++) {
            static::assertTrue($fileSystemEngine->has(...$dataFaked[$i]['key']));
            static::assertTrue($dataFaked[$i]['val'] === $fileSystemEngine->get(...$dataFaked[$i]['key']));
            if ($i % 2 === 0) {
                static::assertTrue($fileSystemEngine->del(...$dataFaked[$i]['key']));
            }
        }

        for ($i = 0; $i < $count; $i++) {
            if ($i % 2 === 0) {
                static::assertFalse($fileSystemEngine->has(...$dataFaked[$i]['key']));
                static::assertNull($fileSystemEngine->get(...$dataFaked[$i]['key']));
            } else {
                static::assertTrue($fileSystemEngine->has(...$dataFaked[$i]['key']));
                static::assertNotNull($fileSystemEngine->get(...$dataFaked[$i]['key']));
            }
        }

        sleep(15);

        foreach ($dataFaked as $data) {
            static::assertFalse($fileSystemEngine->has(...$data['key']));
            static::assertNull($fileSystemEngine->get(...$data['key']));
        }

        $fileSystemEngine->flushAll();
    }

    /**
     * @group CacheEngine
     * @group CacheEngineFilesystem
     */
    public function testIsSupportedDecider()
    {
        $type = $this->getNewHandlerTypeNotSupported();

        static::assertFalse($type->isSupported());
    }

    public function tearDown()
    {
        fclose($this->testResource);

        parent::tearDown();
    }
}

/* EOF */
