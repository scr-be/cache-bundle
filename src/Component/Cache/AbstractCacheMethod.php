<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Component\Cache;

use Scribe\CacheBundle\DependencyInjection\Aware\KeyGeneratorAwareTrait;
use Scribe\Wonka\Exception\RuntimeException;
use Scribe\WonkaBundle\Component\DependencyInjection\Compiler\Attendant\AbstractCompilerAttendant;

/**
 * Class AbstractCacheMethod.
 */
abstract class AbstractCacheMethod extends AbstractCompilerAttendant implements CacheMethodInterface
{
    use KeyGeneratorAwareTrait;

    /**
     * Active TTL for cache entries.
     *
     * @var int
     */
    protected $ttl;

    /**
     * Default TTL for cache entries.
     *
     * @var int
     */
    protected $defaultTtl;

    /**
     * Enabled flag for this cache handler.
     *
     * @var bool
     */
    protected $enabled;

    /**
     * @var bool
     */
    protected $initialized;

    /**
     * Setup cache method with the required properties. Lazy initialization defined in {@see setUp()}.
     *
     * @param bool $enabled
     * @param int  $ttl
     */
    final public function __construct($enabled, $ttl = 0)
    {
        parent::__construct([
            'ttl' => (int) $ttl,
            'defaultTtl' => (int) $ttl,
            'enabled' => (bool) $enabled,
            'initialized' => false,
        ]);
    }

    /**
     * Destruct cache method if initialized. Implementation defined in {@see tearDown()}.
     */
    final public function __destruct()
    {
        if ($this->isInitialized()) {
            $this->tearDown();
        }
    }

    /**
     * Determines if the cache method has been initialized.
     *
     * @return bool
     */
    public function isInitialized()
    {
        return (bool) $this->initialized;
    }

    /**
     * Set the TTL for the cache entries.
     *
     * @param int $seconds
     *
     * @return $this
     */
    final public function setTtl($seconds)
    {
        $this->ttl = (int) $seconds;

        return $this;
    }

    /**
     * Set the TTL back to default.
     *
     * @return $this
     */
    final public function resetTtl()
    {
        $this->ttl = $this->defaultTtl;

        return $this;
    }

    /**
     * Set the enabled/disabled state.
     *
     * @param bool|true $state
     *
     * @return $this
     */
    final public function setEnabled($state = true)
    {
        $this->enabled = (bool) $state;

        return $this;
    }

    /**
     * Get the enabled/disabled state.
     *
     * @return bool
     */
    final public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Get cache value.
     *
     * @param mixed,... $keyValues
     *
     * @return null|mixed
     */
    final public function get(...$keyValues)
    {
        $this->initialize();

        return $this->getCacheEntry(
            $this->getKey(...$keyValues)
        );
    }

    /**
     * Set cache value.
     *
     * @param mixed     $data
     * @param mixed,... $keyValues
     *
     * @return bool
     */
    final public function set($data, ...$keyValues)
    {
        $this->initialize();

        return (bool) $this->setCacheEntry(
            $this->getKey(...$keyValues),
            $data
        );
    }

    /**
     * Check for cache value.
     *
     * @param mixed,... $keyValues
     *
     * @return bool
     */
    final public function has(...$keyValues)
    {
        $this->initialize();

        return (bool) $this->hasCacheEntry(
            $this->getKey(...$keyValues)
        );
    }

    /**
     * Delete cache entry.
     *
     * @param mixed,... $keyValues
     *
     * @return bool
     */
    final public function del(...$keyValues)
    {
        $this->initialize();

        return (bool) $this->delCacheEntry(
            $this->getKey(...$keyValues)
        );
    }

    /**
     * Flush all cache entries.
     *
     * @return bool
     */
    final public function flush()
    {
        $this->initialize();

        return (bool) $this->flushCacheEntries();
    }

    /**
     * Lazily initialization of cache method deferred until cache operation.
     *
     * @return $this
     */
    final protected function initialize()
    {
        if (!$this->enabled) {
            throw new RuntimeException('Disabled cache method "%s" cannot be initialized.', null, null, $this->getType(true));
        }

        if ($this->initialized === false) {
            $this->setUp();
            $this->initialized = true;
        }

        return $this;
    }

    /**
     * Perform any setup on lazy initialization (deferred until call to cache operator).
     */
    protected function setUp()
    {
    }

    /**
     * Perform any cleanup on destruction of class method.
     */
    protected function tearDown()
    {
    }

    /**
     * Get cache entry.
     *
     * @param string $key
     *
     * @return null|mixed
     */
    abstract protected function getCacheEntry($key);

    /**
     * Set cache entry.
     *
     * @param string $key
     * @param mixed  $data
     *
     * @return bool
     */
    abstract protected function setCacheEntry($key, $data);

    /**
     * Check for cache entry.
     *
     * @param string $key
     *
     * @return bool
     */
    abstract protected function hasCacheEntry($key);

    /**
     * Delete cache entry.
     *
     * @param string $key
     *
     * @return bool
     */
    abstract protected function delCacheEntry($key);

    /**
     * Flush all cache entries.
     *
     * @return bool
     */
    abstract protected function flushCacheEntries();
}

/* EOF */
