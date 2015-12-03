<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Tests\Component\Generator\KeyGenerator;

use Scribe\WonkaBundle\Utility\TestCase\WonkaTestCase;
use Scribe\Wonka\Utility\Serializer\Serializer;
use Scribe\CacheBundle\Component\Generator\KeyGenerator;

/**
 * Class KeyGeneratorTest.
 */
class KeyGeneratorTest extends WonkaTestCase
{
    public function test_interface()
    {
        self::assertInstanceOf('Scribe\\CacheBundle\\Component\\Generator\\KeyGeneratorInterface', new KeyGenerator());
    }

    public function test_set_prefix()
    {
        $g = new KeyGenerator();
        $g->setPrefix('test-prefix');

        self::assertEquals('test-prefix', $g->getPrefix());
    }

    public function test_set_algorithm()
    {
        $g = new KeyGenerator();

        foreach (hash_algos() as $algorithm) {
            $g->setAlgorithm($algorithm);
            self::assertEquals($algorithm, $g->getAlgorithm());
        }
    }

    public function test_set_algorithm_invalid()
    {
        self::setExpectedException('Scribe\\Wonka\\Exception\\InvalidArgumentException');

        $g = new KeyGenerator();
        $g->setAlgorithm('invalid-hash-algorithm-name');
    }

    public function test_get_key()
    {
        $g = new KeyGenerator();
        $g->getKey('force-key-compilation');

        $keySets = [
            [1000],
            ['string'],
            [new \DateTime()],
            [0, 1, 2],
            ['one', 'two', 'three'],
            [new \DateTime(), new \DateInterval('P89D'), new \DatePeriod(new \DateTime(), new \DateInterval('P89D'), new \DateTime())],
            [new \DateTime(), 'string', 100, ['an', 'array']],
        ];

        $prefixSet = ['', 'prefix-1', 'another-prefix'];

        foreach (hash_algos() as $algorithm) {
            foreach (range(0, count($keySets) - 1) as $i) {
                foreach (range(0, count($prefixSet) - 1) as $j) {
                    $expected = $prefixSet[$j].hash($algorithm, Serializer::sleep($keySets[$i]), false);
                    $g->setAlgorithm($algorithm);
                    $g->setPrefix($prefixSet[$j]);
                    $g->resetState();
                    $g->getKey('not-matching-key-value');
                    self::assertNotEmpty($expected, $g->getKey());
                    self::assertEquals($expected, $g->getKey(...$keySets[$i]));
                    self::assertEquals($expected, $g->getKey());
                }
            }
        }
    }

    public function test_get_key_invalid()
    {
        self::setExpectedException('Scribe\\Wonka\\Exception\\RuntimeException');

        $g = new KeyGenerator();
        $g->getKey();
    }
}

/* EOF */
