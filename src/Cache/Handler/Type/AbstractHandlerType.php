<?php
/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Cache\Handler\Type;

use Scribe\CacheBundle\Cache\Handler\AbstractHandler;
use Scribe\CacheBundle\Cache\Handler\HandlerInterface;
use Scribe\CacheBundle\Exceptions\InvalidArgumentException;
use Scribe\CacheBundle\Exceptions\RuntimeException;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorAwareTrait;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorInterface;

/**
 * Class AbstractHandlerType
 *
 * @package Scribe\CacheBundle\Cache\Handler\Type
 */
abstract class AbstractHandlerType extends AbstractHandler implements HandlerInterface, HandlerTypeInterface
{
    use KeyGeneratorAwareTrait;

    protected $cacheTtl;

    /**
     * Setup the class instance with the required properties
     *
     * @param KeyGeneratorInterface $keyGenerator
     */
    public function __construct(KeyGeneratorInterface $keyGenerator = null, $ttl = 600)
    {
        $this
            ->setKeyGenerator($keyGenerator)
            ->setTtl($ttl)
        ;
    }

    /**
     * Handler-specific implementation to determine if the caching method is
     * supported by the current platform
     *
     * @return bool
     */
    abstract public function isSupported();

    /**
     * Set the value(s) that create the cache key
     *
     * @param  ...mixed $keyValues
     * @return $this
     */
    public function setKey(...$values)
    {
        $this
            ->getKeyGenerator()
            ->setKeyValues(...$values)
        ;

        return $this;
    }

    /**
     * Get the compiled key string
     *
     * @return string
     */
    public function getKey()
    {
        return (string) $this->getKeyGenerator()->getKey();
    }

    /**
     * Check if a key has been setup
     *
     * @return bool
     */
    public function hasKey()
    {
        return (bool) (true === $this->getKeyGenerator()->hasKeyValues());
    }

    /**
     * Set the time to live for the cache values
     *
     * @param  int $seconds
     * @return $this
     */
    public function setTtl($seconds)
    {
        $this->cacheTtl = (int) $seconds;

        return $this;
    }

    /**
     * Get the TTL for the cache values
     *
     * @return int
     */
    public function getTtl()
    {
        return (int) $this->cacheTtl;
    }

    /**
     * Attempt to get a cached value; returns null if value does not exist or
     * is stale.
     *
     * @param  ...mixed $keyValues
     * @return string|int|object|callable|null
     */
    public function get(...$keyValues)
    {
        $data = $this->getValueViaHandlerImplementation(
            $this->getOrSetKey(...$keyValues)
        );

        return $this->sanitizeReturnedCacheData($data);
    }

    /**
     * Get the cached value. Implementation specific to the handler being used.
     *
     * @param  string $key
     * @return string
     */
    abstract protected function getValueViaHandlerImplementation($key);

    /**
     * Set a cached value; will overwrite a value with the same key silently
     *
     * @param  string|int|object|callable $data
     * @param  ...mixed                   $keyValues
     * @return true
     */
    public function set($data, ...$keyValues)
    {
        return $this->setValueViaHandlerImplementation(
            $this->sanitizeSubmittedCacheData($data),
            $this->getOrSetKey(...$keyValues)
        );
    }

    /**
     * Set the cached value. Implementation specific to the handler being used.
     *
     * @param  string $data
     * @param  string $key
     * @return $this
     */
    abstract protected function setValueViaHandlerImplementation($data, $key);

    /**
     * Check for non-stale existence of cached value with same key
     *
     * @param  ...mixed $keyValues
     * @return bool
     */
    public function has(...$keyValues)
    {
        return (bool) $this->hasValueViaHandlerImplementation(
            $this->getOrSetKey(...$keyValues)
        );
    }

    /**
     * Check for the cached value. Implementation specific to the handler being used.
     *
     * @param  string $key
     * @return bool
     */
    abstract protected function hasValueViaHandlerImplementation($key);

    /**
     * Get the previously set key or set the key based on the passed values
     *
     * @param ...$keyValues
     * @return string
     * @throws InvalidArgumentException
     */
    protected function getOrSetKey(...$keyValues)
    {
        if (true === (count($keyValues) > 0)) {
            $this->setKey(...$keyValues);

            return $this->getKey();
        }

        if (false === $this->hasKey()) {
            throw new InvalidArgumentException(
                'Cannot attempt to get a cached value without a key to retrieve it using.'
            );
        }

        return $this->getKey();
    }

    /**
     * Return cached data in original form (object, int, string, etc)
     *
     * @param  $data
     * @return mixed|null
     */
    protected function sanitizeReturnedCacheData($data)
    {
        if (null === $data) {

            return null;
        }

        return unserialize($data);
    }

    /**
     * Serialize data to be cached (object, int, string, etc)
     *
     * @param  mixed $data
     * @return string
     * @throws RuntimeException If resource data type is given
     */
    protected function sanitizeSubmittedCacheData($data)
    {
        if (true === is_resource($data)) {
            throw new RuntimeException(
                'You cannot cache a resource data type.'
            );
        }

        return serialize($data);
    }

    /**
     * Get the non-name-spaced class name
     *
     * @return string
     */
    public function getClassName()
    {
        return join('', array_slice(explode('\\', get_class($this)), -1));
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getClassName();
    }
}

/* EOF */
