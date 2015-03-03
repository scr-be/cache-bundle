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
use Scribe\CacheBundle\Exceptions\InvalidArgumentException;
use Scribe\CacheBundle\Exceptions\RuntimeException;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorAwareTrait;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorInterface;

/**
 * Class AbstractHandlerType
 *
 * @package Scribe\CacheBundle\Cache\Handler\Type
 */
abstract class AbstractHandlerType extends AbstractHandler implements HandlerTypeInterface
{
    use KeyGeneratorAwareTrait;

    /**
     * The number of seconds before a cache entry becomes stale
     *
     * @var int
     */
    protected $ttl;

    /**
     * Priority of this cache handler
     *
     * @var int|null
     */
    protected $priority;

    /**
     * Disable flag for this cache handler
     *
     * @var bool
     */
    protected $disabled;

    /**
     * Setup the class instance with the required properties
     *
     * @param KeyGeneratorInterface $keyGenerator
     * @param int                   $ttl
     * @param int|null              $priority
     * @param bool                  $disabled
     */
    public function __construct(KeyGeneratorInterface $keyGenerator = null, $ttl = 1800, $priority = null, $disabled = false)
    {
        $this
            ->setKeyGenerator($keyGenerator)
            ->setTtl($ttl)
            ->setPriority($priority)
            ->setEnabled($disabled !== true)
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
        $this->ttl = (int) $seconds;

        return $this;
    }

    /**
     * Get the TTL for the cache values
     *
     * @return int
     */
    public function getTtl()
    {
        return (int) $this->ttl;
    }

    /**
     * Set the cache handler priority
     *
     * @param  int|null $priority
     * @return $this
     */
    public function setPriority($priority = null)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get the cache handler priority
     *
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Check if cache handler has a priority
     *
     * @return bool
     */
    public function hasPriority()
    {
        return (bool) ($this->priority !== null);
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
        $data = $this->getUsingHandler(
            $this->getCurrentKey(...$keyValues)
        );

        return $this->sanitizeReturnedCacheData($data);
    }

    /**
     * Get the cached value. Implementation specific to the handler being used.
     *
     * @param  string $key
     * @return string
     */
    abstract protected function getUsingHandler($key);

    /**
     * Set a cached value; will overwrite a value with the same key silently
     *
     * @param  string|int|object|callable $data
     * @param  ...mixed                   $keyValues
     * @return bool
     */
    public function set($data, ...$keyValues)
    {
        return $this->setUsingHandler(
            $this->sanitizeSubmittedCacheData($data),
            $this->getCurrentKey(...$keyValues)
        );
    }

    /**
     * Set the cached value. Implementation specific to the handler being used.
     *
     * @param  string $data
     * @param  string $key
     * @return $this
     */
    abstract protected function setUsingHandler($data, $key);

    /**
     * Check for non-stale existence of cached value with same key
     *
     * @param  ...mixed $keyValues
     * @return bool
     */
    public function has(...$keyValues)
    {
        return (bool) $this->hasUsingHandler(
            $this->getCurrentKey(...$keyValues)
        );
    }

    /**
     * Check for the cached value. Implementation specific to the handler being used.
     *
     * @param  string $key
     * @return bool
     */
    abstract protected function hasUsingHandler($key);

    /**
     * Delete a cache value
     *
     * @param  ...mixed $keyValues
     * @return bool
     */
    public function del(...$keyValues)
    {
        return (bool) $this->delUsingHandler(
            $this->getCurrentKey(...$keyValues)
        );
    }

    /**
     * Check for the cached value. Implementation specific to the handler being used.
     *
     * @param  string $key
     * @return bool
     */
    abstract protected function delUsingHandler($key);

    /**
     * Flush all cached values (this could potentially be more than values stored
     * using only this API, but also, for instance, Doctrine's APCu cache if using
     * the APUc handler).
     *
     * @return bool
     */
    public function flushAll()
    {
        return (bool) $this->flushAllUsingHandler();
    }

    /**
     * Flush all cached values
     *
     * @return bool
     */
    abstract protected function flushAllUsingHandler();

    /**
     * Get the previously set key or set the key based on the passed values
     *
     * @param ...$keyValues
     * @return string
     * @throws InvalidArgumentException
     */
    protected function getCurrentKey(...$keyValues)
    {
        if (true === (count($keyValues) > 0)) {
            $this->setKey(...$keyValues);

            return $this->getKey();
        }

        if (false === $this->hasKey()) {
            throw new InvalidArgumentException(
                'Cannot attempt to get a cached value without setting a key to retrieve it.'
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
     * Get the handler type
     *
     * @return string
     */
    public function getType()
    {
        return (string) strtolower(str_replace(
            'HandlerType', '',
            join('', array_slice(explode('\\', $this->__toString()), -1))
        ));
    }

    /**
     * Type casting object will return its fully-qualified class name
     *
     * @return string
     */
    public function __toString()
    {
        return (string) get_class($this);
    }
}

/* EOF */
