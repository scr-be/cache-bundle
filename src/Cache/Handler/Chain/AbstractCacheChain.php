<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Cache\Handler\Chain;

use Scribe\CacheBundle\Cache\Handler\AbstractHandler;
use Scribe\CacheBundle\Cache\Handler\Engine\AbstractCacheEngine;
use Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineInterface;
use Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineMock;
use Scribe\CacheBundle\Exceptions\RuntimeException;
use Scribe\CacheBundle\Exceptions\InvalidArgumentException;
use Scribe\Component\DependencyInjection\Compiler\CompilerPassChainInterface;
use Scribe\Component\DependencyInjection\Compiler\CompilerPassChainTrait;
use Scribe\Component\DependencyInjection\Compiler\CompilerPassHandlerInterface;

/**
 * Class AbstractCacheChain.
 */
abstract class AbstractCacheChain extends AbstractHandler implements CacheChainInterface
{
    use CompilerPassChainTrait;

    /**
     * The handler with the highest priority.
     *
     * @var CacheEngineInterface|null
     */
    protected $activeHandler = null;

    /**
     * Setup the object instance properties.
     *
     * @param bool $disabled
     */
    public function __construct($disabled = false)
    {
        $this->handlers     = [];
        $this->filterMode   = CompilerPassChainInterface::FILTER_MODE_FIRST;
        $this->restrictions = [
            CompilerPassChainInterface::RESTRICTION_INTERFACE_DEFAULT,
            CacheEngineInterface::INTERFACE_NAME_CACHE,
        ];

        $this->setEnabled(true !== $disabled);
    }

    /**
     * Basic implementation of the compiler pass add handler.
     *
     * @param CompilerPassHandlerInterface $handler
     * @param int|null                     $priority
     *
     * @return $this
     */
    public function addHandler(CompilerPassHandlerInterface $handler, $priority = null)
    {
        $this
            ->determineStackPosition($handler)
            ->determineActiveHandler()
        ;

        return $this;
    }

    /**
     * @param mixed $by
     *
     * @throws RuntimeException
     * @throws InvalidArgumentException
     *
     * @return \Scribe\CacheBundle\Cache\Handler\Engine\AbstractCacheEngine
     */
    public function getHandler(...$by)
    {
        if (1 !== count($by)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid number of arguments provided to "%s" in "%s".',
                    __FUNCTION__,
                    __CLASS__
                )
            );
        }

        list($type) = $by;
        $type = strtolower($type);

        foreach ($this->handlers as $h) {
            if ($h->getType() === $type) {
                return $h;
            }
        }

        throw new RuntimeException(
            'The requested handler type "%s" is not available in "%s".',
            null, null, null, $type, __METHOD__
        );
    }

    /**
     * Re-determine active handler, possibly based on forced selection.
     *
     * @param string|null $forceType
     *
     * @return AbstractCacheChain
     */
    public function reDetermineActiveHandler($forceType = null)
    {
        return $this->determineActiveHandler($forceType);
    }

    /**
     * Sets the active handler.
     *
     * @param CompilerPassHandlerInterface $handler
     *
     * @return $this
     */
    public function setActiveHandler(CompilerPassHandlerInterface $handler)
    {
        $this->activeHandler = $handler;

        return $this;
    }

    /**
     * Gets the active handler.
     *
     * @return \Scribe\CacheBundle\Cache\Handler\Engine\AbstractCacheEngine
     *
     * @throws RuntimeException
     */
    public function getActiveHandler()
    {
        if (false === $this->isEnabled()) {
            return $this->getForcedMockHandler();
        }

        if ($this->activeHandler instanceof AbstractCacheEngine) {
            return $this->activeHandler;
        }

        throw new RuntimeException(
            'No enabled/supported cache engines are configured; you must configure at least one or globally disable this bundle (in "%s").',
            null, null, null, __METHOD__
        );
    }

    /**
     * Returns a mocked handler so a valid Handler API (that doesn't actually mock) is always available.
     *
     * @internal
     *
     * @return \Scribe\CacheBundle\Cache\Handler\Engine\CacheEngineMock
     */
    public function getForcedMockHandler()
    {
        if ($this->activeHandler instanceof CacheEngineMock) {
            return $this->activeHandler;
        }

        $mockHandler = new CacheEngineMock();

        $this->setActiveHandler($mockHandler);

        return $mockHandler;
    }

    /**
     * Checks if an active handler has been set.
     *
     * @param bool $filterOutMockedHandlers Don't include implementations of Mocked handlers when determining if
     *                                      a valid active handler is set.
     *
     * @return bool
     */
    public function hasActiveHandler($filterOutMockedHandlers = true)
    {

        return (bool) ($this->activeHandler instanceof AbstractCacheEngine);
    }

    /**
     * Get the active handler type, by default the short name of class such as
     * simply "apcu" but optionally return the fully-qualified class name.
     *
     * @param bool $fullyQualified
     *
     * @return string
     */
    public function getActiveHandlerType($fullyQualified = false)
    {
        return (string) $this->getActiveHandler()->getType($fullyQualified);
    }

    /**
     * Quite literally un-sets the chosen active handler. Calling {@see getActiveHandler()} without
     * re-determining the active handler will provide you with a mocked cache implementation.
     *
     * @return $this
     */
    public function clearActiveHandlerType()
    {
        $this->activeHandler = null;

        return $this;
    }

    /**
     * Set the value(s) that create the cache key.
     *
     * @param ...mixed $keyValues
     *
     * @return $this
     */
    public function setKey(...$keyValues)
    {
        $this->getActiveHandler()->setKey(...$keyValues);

        return $this;
    }

    /**
     * Get the compiled key string.
     *
     * @return string|null
     */
    public function getKey()
    {
        return $this->getActiveHandler()->getKey();
    }

    /**
     * Check if a key has been setup.
     *
     * @return bool
     */
    public function hasKey()
    {
        return $this->getActiveHandler()->hasKey();
    }

    /**
     * Attempt to get a cached value; returns null if value does not exist or
     * is stale.
     *
     * @param ...mixed $keyValues
     *
     * @return string|int|object|callable|null
     */
    public function get(...$keyValues)
    {
        return $this->getActiveHandler()->get(...$keyValues);
    }

    /**
     * Set a cached value; will overwrite a value with the same key silently.
     *
     * @param string|int|object|callable $data
     * @param ...mixed                   $keyValues
     *
     * @return bool
     */
    public function set($data, ...$keyValues)
    {
        return $this->getActiveHandler()->set($data, ...$keyValues);
    }

    /**
     * Check for non-stale existence of cached value with same key.
     *
     * @param ...$keyValues
     *
     * @return bool
     */
    public function has(...$keyValues)
    {
        return $this->getActiveHandler()->has(...$keyValues);
    }

    /**
     * Delete the cached data using the provided key.
     *
     * @param ...mixed $keyValues
     *
     * @return bool
     */
    public function del(...$keyValues)
    {
        return $this->getActiveHandler()->del(...$keyValues);
    }

    /**
     * Flush all cached data within this cache mechanism-type.
     *
     * @return bool
     */
    public function flushAll()
    {
        return $this->getActiveHandler()->flushAll();
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
        $this->getActiveHandler()->setTtl($seconds);

        return $this;
    }

    /**
     * Get the TTL for the cache values.
     *
     * @return int
     */
    public function getTtl()
    {
        return $this->getActiveHandler()->getTtl();
    }

    /**
     * Set the TTL back to the system default.
     *
     * @return $this
     */
    public function setTtlToDefault()
    {
        $this->getActiveHandler()->setTtlToDefault();

        return $this;
    }

    /**
     * Each time a new handler is added to the stack, re-determine the active
     * handler by processing them by priority (index value) and checking for the
     * first handler type that is both enabled and supported.
     *
     * @throws RuntimeException
     *
     * @param null|string|\Scribe\CacheBundle\Cache\Handler\Engine\AbstractCacheEngine $forceType
     *
     * @return $this
     */
    abstract protected function determineActiveHandler($forceType = null);

    /**
     * Stack the provided handler in the correct position on the handlers stack,
     * verifying that another handler does not already have the same priority.
     *
     * @param CompilerPassHandlerInterface $handler
     *
     * @return $this
     */
    abstract protected function determineStackPosition(CompilerPassHandlerInterface $handler);
}

/* EOF */
