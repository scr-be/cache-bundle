<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Cache\Handler\Engine;

use Scribe\Utility\ClassInfo;
use Scribe\Utility\Serializer\Serializer;
use Scribe\CacheBundle\Cache\Handler\AbstractHandler;
use Scribe\CacheBundle\Exceptions\InvalidArgumentException;
use Scribe\CacheBundle\Exceptions\RuntimeException;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorAwareTrait;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorInterface;

/**
 * Class AbstractCacheEngine.
 */
abstract class AbstractCacheEngine extends AbstractHandler implements CacheEngineInterface
{
    use KeyGeneratorAwareTrait;

    /**
     * The versions of the relevant software interaction layer.
     *
     * @var array
     */
    protected $versions = [];

    /**
     * If {@see AbstractCacheEngine::$versions} is null, this callable can be
     * optionally defined to determine the versions of the relevant software
     * interaction layer.
     *
     * @var callable|null
     */
    protected $versionsDeterminer = null;

    /**
     * The number of seconds before a cache entry becomes stale.
     *
     * @var int
     */
    protected $ttl;

    /**
     * The default number of seconds before a cache entry becomes stale.
     *
     * @var int
     */
    protected $ttlDefault;

    /**
     * Priority of this cache handler.
     *
     * @var int|null
     */
    protected $priority;

    /**
     * Disable flag for this cache handler.
     *
     * @var bool
     */
    protected $disabled;

    /**
     * Optional closure to determine if cache handler is supported.
     *
     * @var callable|null
     */
    protected $supportedDecider;

    /**
     * @var bool
     */
    protected $initialized;

    /**
     * Setup the class instance with the required properties.
     *
     * @param KeyGeneratorInterface|null $keyGenerator
     * @param int                        $ttl
     * @param int|null                   $priority
     * @param bool                       $disabled
     * @param callable|null              $supportedDecider
     */
    public function __construct(KeyGeneratorInterface $keyGenerator = null, $ttl = 1800, $priority = null, $disabled = false, callable $supportedDecider = null)
    {
        $this->ttlDefault = $ttl;

        $this
            ->setKeyGenerator($keyGenerator)
            ->setTtl($ttl)
            ->setPriority($priority)
            ->setEnabled($disabled === false)
            ->setSupportedDecider($supportedDecider)
            ->setInitialized(true)
        ;
    }

    /**
     * Type casting object will return its fully-qualified class name.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getType(true);
    }

    /**
     * Get the handler type.
     *
     * @param bool $fqcn
     *
     * @return string
     */
    public function getType($fqcn = false)
    {
        if ($fqcn === true) {
            return (string) ClassInfo::getNamespaceByInstance($this).ClassInfo::getClassNameByInstance($this);
        }

        return (string) strtolower(
            str_replace('CacheEngine', '', ClassInfo::getClassNameByInstance($this))
        );
    }

    /**
     * @return boolean
     */
    public function isInitialized()
    {
        return (bool) $this->initialized;
    }

    /**
     * @param boolean $initialized
     *
     * @return $this
     */
    public function setInitialized($initialized)
    {
        $this->initialized = $initialized;

        return $this;
    }

    /**
     * Set the optional closure that determines if this cache handler is supported.
     *
     * @param callable|null $decider
     *
     * @return $this
     */
    public function setSupportedDecider(callable $decider = null)
    {
        if (true === ($decider instanceof \Closure)) {
            $this->supportedDecider = $decider;
        }

        return $this;
    }

    /**
     * Get the optional closure that determines if this cache handler is supported.
     *
     * @return callable|null
     */
    public function getSupportedDecider()
    {
        return $this->supportedDecider;
    }

    /**
     * Check if the optional closure that determines if this cache handler is supported has been set.
     *
     * @return bool
     */
    public function hasSupportedDecider()
    {
        return (bool) ($this->supportedDecider instanceof \Closure);
    }

    /**
     * Un-set the optional closure that determines if this cache handler is supported.
     *
     * @return $this
     */
    public function clearSupportedDecider()
    {
        $this->supportedDecider = null;

        return $this;
    }

    /**
     * Check if the handler type is supported by the current environment based *first* an optional user-specified
     * callback decider, or if one is not defined, the default decider implementation for the given type.
     *
     * @return bool
     */
    public function isSupported(...$by)
    {
        if (null !== ($decision = $this->isSupportedUserDecider(...$by))) {
            return (bool) $decision;
        }

        return (bool) $this->isSupportedDefaultDecider(...$by);
    }

    /**
     * Attempt to call the optional closure that determines if this cache handler is supported.
     *
     * @param mixed,... $by
     *
     * @return bool|null
     */
    protected function isSupportedUserDecider(...$by)
    {
        if (false === $this->hasSupportedDecider()) {
            return;
        }

        $decider = $this->getSupportedDecider();

        return (bool) ($decider(...$by));
    }

    /**
     * Check if the handler type is supported using the default decider implementation.
     *
     * @param mixed,... $by
     *
     * @return bool
     */
    abstract protected function isSupportedDefaultDecider(...$by);

    /**
     * Set the value(s) that create the cache key.
     *
     * @param mixed,... $keyValues
     *
     * @return $this
     */
    public function setKey(...$keyValues)
    {
        $this
            ->getKeyGenerator()
            ->setKeyValues(...$keyValues)
        ;

        return $this;
    }

    /**
     * Get the compiled key string.
     *
     * @return string
     */
    public function getKey()
    {
        return (string) $this->getKeyGenerator()->getKey();
    }

    /**
     * Check if a key has been setup.
     *
     * @return bool
     */
    public function hasKey()
    {
        return (bool) (true === $this->getKeyGenerator()->hasKeyValues());
    }

    /**
     * Set the time to live for the cache values.
     *
     * @param int $seconds
     *
     * @return $this
     */
    public function setTtl($seconds)
    {
        $this->ttl = (int) $seconds;

        return $this;
    }

    /**
     * Get the TTL for the cache values.
     *
     * @return int
     */
    public function getTtl()
    {
        return (int) $this->ttl;
    }

    /**
     * Set the TTL back to the system default.
     *
     * @return $this
     */
    public function setTtlToDefault()
    {
        $this->setTtl($this->ttlDefault);
    }

    /**
     * Set the cache handler priority.
     *
     * @param int|null $priority
     *
     * @return $this
     */
    public function setPriority($priority = null)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get the cache handler priority.
     *
     * @return int|null
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Check if cache handler has a priority.
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
     * @param mixed,... $keyValues
     *
     * @return string|int|object|callable|null
     */
    public function get(...$keyValues)
    {
        if ($this->lazyInitialize() === false) {
            return null;
        }

        $data = $this->getUsingHandler(
            $this->getCurrentKey(...$keyValues)
        );

        return $this->sanitizeReturnedCacheData($data);
    }

    /**
     * Get the cached value. Implementation specific to the handler being used.
     *
     * @param string $key
     *
     * @return string
     */
    abstract protected function getUsingHandler($key);

    /**
     * Set a cached value; will overwrite a value with the same key silently.
     *
     * @param string|int|object|callable  $data
     * @param mixed,...                   $keyValues
     *
     * @return bool
     */
    public function set($data, ...$keyValues)
    {
        if ($this->lazyInitialize() === false) {
            return false;
        }

        return $this->setUsingHandler(
            $this->sanitizeSubmittedCacheData($data),
            $this->getCurrentKey(...$keyValues)
        );
    }

    /**
     * Set the cached value. Implementation specific to the handler being used.
     *
     * @param string $data
     * @param string $key
     *
     * @return bool
     */
    abstract protected function setUsingHandler($data, $key);

    /**
     * Check for non-stale existence of cached value with same key.
     *
     * @param ...mixed $keyValues
     *
     * @return bool
     */
    public function has(...$keyValues)
    {
        if ($this->lazyInitialize() === false) {
            return false;
        }

        return (bool) $this->hasUsingHandler(
            $this->getCurrentKey(...$keyValues)
        );
    }

    /**
     * Check for the cached value. Implementation specific to the handler being used.
     *
     * @param string $key
     *
     * @return bool
     */
    abstract protected function hasUsingHandler($key);

    /**
     * Delete a cache value.
     *
     * @param ...mixed $keyValues
     *
     * @return bool
     */
    public function del(...$keyValues)
    {
        if ($this->lazyInitialize() === false) {
            return false;
        }

        return (bool) $this->delUsingHandler(
            $this->getCurrentKey(...$keyValues)
        );
    }

    /**
     * Check for the cached value. Implementation specific to the handler being used.
     *
     * @param string $key
     *
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
        if ($this->lazyInitialize() === false) {
            return false;
        }

        return (bool) $this->flushAllUsingHandler();
    }

    /**
     * Flush all cached values.
     *
     * @return bool
     */
    abstract protected function flushAllUsingHandler();

    /**
     * By default there is nothing to lazy initialize.
     *
     * @return bool
     */
    protected function lazyInitialize()
    {
        return true;
    }

    /**
     * Get the previously set key or set the key based on the passed values.
     *
     * @param mixed $keyValues,...
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    protected function getCurrentKey(...$keyValues)
    {
        if (count($keyValues) > 0) {
            $this->setKey(...$keyValues);
        }

        if (true === $this->hasKey()) {
            return $this->getKey();
        }

        throw new InvalidArgumentException(
            'Cannot attempt to get a cached value without setting a key to retrieve it in %s.',
            null, null, null, __METHOD__
        );
    }

    /**
     * Return cached data in original form (object, int, string, etc).
     *
     * @param string $data
     *
     * @return mixed|null
     */
    protected function sanitizeReturnedCacheData($data)
    {
        return ($data !== null ? Serializer::wake($data) : null);
    }

    /**
     * Serialize data to be cached (object, int, string, etc). Cannot be a resource type.
     *
     * @param mixed $data
     *
     * @throws RuntimeException If resource data type is given
     *
     * @return string
     */
    protected function sanitizeSubmittedCacheData($data)
    {
        if (is_resource($data) === false) {
            return Serializer::sleep($data);
        }

        throw new RuntimeException(
            'As a resource cannot be serialized it cannot be passed as a cache value in "%s".',
            null, null, null, __METHOD__
        );
    }
}

/* EOF */
