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

namespace Scribe\Teavee\ObjectCacheBundle\Component\Cache\Redis;

use Redis;
use Scribe\Teavee\ObjectCacheBundle\Component\Cache\AbstractCacheAttendant;
use Scribe\Wonka\Exception\InvalidArgumentException;
use Scribe\Wonka\Exception\LogicException;
use Scribe\Wonka\Utility\Extension;
use Scribe\Wonka\Utility\Filter\StringFilter;

/**
 * Class RedisAttendant.
 */
class RedisAttendant extends AbstractCacheAttendant implements RedisAttendantInterface
{
    /**
     * Options defined by DI; normalized and loaded upon lazy initialization, {@see setUp()}.
     *
     * @var string[]
     */
    protected $optionCollection = [];

    /**
     * Server defined by DI; normalized and loaded upon lazy initialization, {@see setUp()}.
     *
     * @var mixed[]
     */
    protected $server = [];

    /**
     * Redis object instance.
     *
     * @var Redis
     */
    protected $r;

    /**
     * Set server options.
     *
     * @param string[] $options
     *
     * @return $this
     */
    public function setOptions(array $options = [])
    {
        $this->optionCollection = (array) $options;

        return $this;
    }

    /**
     * Get server options.
     *
     * @return string[]
     */
    public function getOptions()
    {
        return $this->optionCollection;
    }

    /**
     * @param mixed[] $server
     *
     * @return $this
     */
    public function setServer(array $server = [])
    {
        $this->server = (array) $server;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getServer()
    {
        return (array) $this->server;
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported(...$by)
    {
        return (bool) (Extension::isEnabled('redis'));
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this
            ->setUpInstance()
            ->setUpServers()
            ->setUpOptions();
    }

    /**
     * Instantiate redis object instance.
     *
     * @return $this
     */
    protected function setUpInstance()
    {
        $this->r = new Redis();

        return $this;
    }

    /**
     * Normalize passed option list and apply.
     *
     * @return $this
     */
    protected function setUpOptions()
    {
        foreach ($this->optionCollection as $type => $state) {
            $this->r->setOption(...$this->normalizeOption($type, $state));
        }

        return $this;
    }

    /**
     * Normalize human-readable options to correct Memcache constants for type and state.
     *
     * @param string   $type
     * @param mixed    $state
     *
     * @return mixed[]
     */
    protected function normalizeOption($type, $state)
    {
        return [
            $this->normalizeOptionType($type),
            $this->normalizeOptionState($state)
        ];
    }

    /**
     * Normalize option type.
     *
     * @param string $type
     *
     * @return mixed
     */
    protected function normalizeOptionType($type)
    {
        return $this->resolveConstant($type, 'OPT_');
    }

    /**
     * Normalize option type state.
     *
     * @param mixed $state
     *
     * @return bool|int
     */
    protected function normalizeOptionState($state)
    {
        return $this->resolveConstant($state);
    }

    /**
     * Resolve value of Redis constant.
     *
     * @param string $name
     * @param string $prefix
     * @param string $class
     *
     * @throws InvalidArgumentException If constant cannot be resolved.
     *
     * @return mixed
     */
    protected function resolveConstant($name, $prefix = '', $class = 'Redis')
    {
        $constant = sprintf('%s::%s%s', $class, $prefix, strtoupper($name));

        if (!defined($constant)) {
            throw new InvalidArgumentException('Provided name "%s" unresolvable to constant "%s".', null, null, $name, $constant);
        }

        return constant($constant);
    }

    /**
     * Normalize passed server list and apply.
     *
     * @return $this
     */
    protected function setUpServers()
    {
        if (true !== $this->r->connect(...$this->normalizeServerOptions($this->server))) {
            throw new InvalidArgumentException('Could not connect to server with keys "%s" and values "%s".', null, null, implode(',', array_keys($this->server)), implode(',', array_values($this->server)));
        }

        return $this;
    }

    /**
     * Normalize server options.
     *
     * @param mixed[] $options
     *
     * @return mixed[]
     */
    protected function normalizeServerOptions(array $options = [])
    {
        $normalized = [];

        foreach (['host', 'port', 'timeout', 'reserved', 'retry_interval'] as $o) {
            $normalized[] = $this->normalizeServerOptionValue($options, $o);
        }

        return $normalized;
    }

    /**
     * @param mixed[] $options
     * @param string  $name
     *
     * @return mixed
     */
    protected function normalizeServerOptionValue(array $options, $name)
    {
        if (array_key_exists($name, $options)) {
            return $options[$name];
        }

        return $this->resolveConstant(strtoupper($name), 'DEFAULT_', get_called_class());
    }

    /**
     * Get cache entry.
     *
     * @param string $key
     *
     * @return null|string
     */
    protected function getCacheEntry($key)
    {
        if (false === ($data = $this->r->get($key))) {
            return;
        }

        return $data;
    }

    /**
     * Set cache entry.
     *
     * @param string $key
     * @param mixed  $data
     *
     * @return bool
     */
    protected function setCacheEntry($key, $data)
    {
        return (bool) $this->r->set($key, $data, $this->ttl);
    }

    /**
     * Check for cache entry.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function hasCacheEntry($key)
    {
        return (bool) $this->r->exists($key);
    }

    /**
     * Delete cache entry.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function delCacheEntry($key)
    {
        return (bool) $this->r->del($key);
    }

    /**
     * Flush all cache entries.
     *
     * @return bool
     */
    protected function flushCacheEntries()
    {
        return (bool) $this->r->flushAll();
    }

    /**
     * @return string[]
     */
    protected function listCacheKeys()
    {
        return (array) $this->r->keys('*');
    }
}

/* EOF */
