<?php
/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Cache\Handlers;

use Scribe\CacheBundle\Exceptions\InvalidArgumentException;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorAwareTrait;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorInterface;

/**
 * Class AbstractHandler
 *
 * @package Scribe\CacheBundle\Cache\Handlers
 */
abstract class AbstractHandler implements HandlerInterface
{
    use KeyGeneratorAwareTrait;

    /**
     * Allows for enabling/disabling this caching method
     *
     * @var bool
     */
    protected $cacheEnabled = true;

    /**
     * Setup the class instance with the required properties
     *
     * @param KeyGeneratorInterface $keyGenerator
     */
    public function __construct(KeyGeneratorInterface $keyGenerator = null)
    {
        $this->setKeyGenerator($keyGenerator);
    }

    /**
     * Handler-specific implementation to determine if the caching method is
     * supported by the current platform
     *
     * @return bool
     */
    abstract public function isSupported();

    /**
     * Set the enabled/disabled state of this cache handler method
     *
     * @param  bool $cacheEnabled
     * @return $this
     */
    public function setEnabled($cacheEnabled = true)
    {
        $this->cacheEnabled = (bool) $cacheEnabled;

        return $this;
    }

    /**
     * Check if this cache handler method is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) (true === $this->cacheEnabled);
    }

    /**
     * Check if this cache handler method is enabled
     *
     * @return bool
     */
    public function isDisabled()
    {
        return (bool) (false === $this->cacheEnabled);
    }

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
     * @param ...$keyValues
     * @return bool
     */
    public function has(...$keyValues)
    {
        return (bool) $this->hasValueViaHandlerImplementation(
            $this->getOrSetKey(...$keyValues)
        );
    }

    /**
     * Set the cached value. Implementation specific to the handler being used.
     *
     * @param  string $data
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
            return (string) $this
                ->setKey($keyValues)
                ->getKey()
            ;
        }

        if (false === $this->hasKey()) {
            throw new InvalidArgumentException(
                'Cannot attempt to get a cached value without a key to retrieve it using.'
            );
        }

        return $this->getKey();
    }

    protected function sanitizeSubmittedCacheData($data)
    {
        
    }
}

/* EOF */
