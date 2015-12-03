<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Component\Generator;

use Scribe\Wonka\Exception\InvalidArgumentException;
use Scribe\Wonka\Exception\RuntimeException;
use Scribe\Wonka\Serializer\SerializerFactory;

/**
 * Class KeyGenerator.
 */
class KeyGenerator implements KeyGeneratorInterface
{
    /**
     * A human-readable string prefixed to the cache key.
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * The generated key.
     *
     * @var null|string
     */
    protected $key = null;

    /**
     * Hash algorithm used to generate key.
     *
     * @var string
     */
    protected $algorithm = 'md5';

    /**
     * Original values used for identifying and creating the generated key.
     *
     * @var null|mixed[]
     */
    protected $valueCollection = null;

    /**
     * Set key prefix.
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = (string) $prefix;
        $this->resetState();

        return $this;
    }

    /**
     * Get key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return (string) $this->prefix;
    }

    /**
     * Set the hash algorithm used to generate key.
     *
     * @param string $algorithm
     *
     * @throws InvalidArgumentException If algorithm is not available.
     *
     * @return $this
     */
    public function setAlgorithm($algorithm)
    {
        if (!in_array((string) $algorithm, hash_algos())) {
            throw new InvalidArgumentException('Invalid hash algorithm of "%s" provided to key generator.', null, null, (string) $algorithm);
        }

        $this->algorithm = (string) $algorithm;
        $this->resetState();

        return $this;
    }

    /**
     * Get the hash algorithm user to generate key.
     *
     * @return string
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * @return $this
     */
    public function resetState()
    {
        $this->valueCollection = null;
        $this->key = null;

        return $this;
    }

    /**
     * Check if key has been compiled and set.
     *
     * @return bool
     */
    public function hasKey()
    {
        return (bool) !($this->key === null || strlen($this->key) === 0);
    }

    /**
     * Get/generate key. If no parameters are provided, previously compiled key returned (if available). If parameters
     * are provided, key is only re-generated when values are different from previous compilation.
     *
     * @param mixed,... $keyValues
     *
     * @throws RuntimeException If no key is available.
     *
     * @return string
     */
    public function getKey(...$keyValues)
    {
        $this->compile(...$keyValues);

        if (!$this->hasKey()) {
            throw new RuntimeException('No generated key available: you must provide key values for successful key generator compilation.');
        }

        return (string) $this->key;
    }

    /**
     * @param mixed,... $keyValues
     *
     * @return $this
     */
    protected function compile(...$keyValues)
    {
        if (count($keyValues) > 0 && $this->valueCollection !== $keyValues) {
            $this->valueCollection = $keyValues;
            $this->key = (string) $this->prefix.hash($this->algorithm, SerializerFactory::create()->serializeData($keyValues), false);
        }

        return $this;
    }
}

/* EOF */
