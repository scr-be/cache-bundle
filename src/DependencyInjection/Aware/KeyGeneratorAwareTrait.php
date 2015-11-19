<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\DependencyInjection\Aware;

use Scribe\CacheBundle\Component\Generator\KeyGeneratorInterface;

/**
 * Trait KeyGeneratorAwareTrait.
 */
trait KeyGeneratorAwareTrait
{
    /**
     * @var KeyGeneratorInterface
     */
    protected $keyGenerator;

    /**
     * Set the key generator instance.
     *
     * @param KeyGeneratorInterface $keyGenerator
     *
     * @return $this
     */
    public function setKeyGenerator(KeyGeneratorInterface $keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;

        return $this;
    }

    /**
     * Get the key generator instance.
     *
     * @return KeyGeneratorInterface
     */
    public function getKeyGenerator()
    {
        return $this->keyGenerator;
    }

    /**
     * Get the compiled key string.
     *
     * @param mixed,... $keyValues
     *
     * @return string
     */
    public function getKey(...$keyValues)
    {
        $key = (string) $this
            ->keyGenerator
            ->getKey(...$keyValues);

        return $key;
    }

    /**
     * Set the compiled key string.
     *
     * @param mixed,... $keyValues
     *
     * @return $this
     */
    public function setKey(...$keyValues)
    {
        $this
            ->keyGenerator
            ->getKey(...$keyValues);

        return $this;
    }
}

/* EOF */
