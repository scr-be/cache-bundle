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
use Scribe\CacheBundle\Cache\Handler\Type\AbstractHandlerType;
use Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeMockery;
use Scribe\CacheBundle\Exceptions\RuntimeException;

/**
 * Class AbstractHandlerChain.
 */
abstract class AbstractHandlerChain extends AbstractHandler implements HandlerChainInterface
{
    /**
     * An array of cache handlers ordered by priority provided via a dependency
     * injection compiler pass. They have not been filtered for their internal
     * enabled or supported states.
     *
     * @var AbstractHandlerType[]
     */
    protected $handlers = [ ];

    /**
     * The handler with the highest priority.
     *
     * @var AbstractHandlerType|null
     */
    protected $activeHandler = null;

    /**
     * Setup the object instance properties.
     *
     * @param bool $disabled
     */
    public function __construct($disabled = false)
    {
        $this->setEnabled($disabled !== true);
    }

    /**
     * Add a cache handler type to the stack of tagged handlers.
     *
     * @param AbstractHandlerType $handler
     */
    public function addHandler(AbstractHandlerType $handler)
    {
        $this
            ->determineStackPosition($handler)
            ->determineActiveHandler()
        ;
    }

    /**
     * Sets an array of handlers (clearing any previous ones).
     *
     * @param AbstractHandlerType[] $handlers
     *
     * @return $this
     */
    public function setHandlers(array $handlers = [ ])
    {
        $this->handlers      = [ ];
        $this->activeHandler = null;

        foreach ($handlers as $h) {
            $this->addHandler($h);
        }

        return $this;
    }

    /**
     * Returns the handler from the stack.
     *
     * @return AbstractHandlerType[]
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * Check if any handlers have been registered.
     *
     * @return bool
     */
    public function hasHandlers()
    {
        return (bool) (true === (count($this->handlers)) > 0);
    }

    /**
     * Sets the active handler.
     *
     * @param AbstractHandlerType $handler
     *
     * @return $this
     */
    abstract protected function setActiveHandler(AbstractHandlerType $handler);

    /**
     * Gets the active handler.
     *
     * @return AbstractHandlerType|null
     *
     * @throws RuntimeException
     */
    public function getActiveHandler()
    {
        if (true === $this->hasActiveHandler() && true === $this->isEnabled()) {
            return $this->activeHandler;
        }

        if (false === $this->isEnabled()) {
            if (false === ($this->activeHandler instanceof HandlerTypeMockery)) {
                $this->activeHandler = new HandlerTypeMockery();
            }

            return $this->activeHandler;
        }

        throw new RuntimeException(
            'No enabled and supported cache handler types have been configured. '.
            'You must configure at least one type or globally disable this bundle.'
        );
    }

    /**
     * Checks if an active handler has been set.
     *
     * @return bool
     */
    public function hasActiveHandler()
    {
        return (bool) ($this->activeHandler instanceof AbstractHandlerType);
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
     * Stack the provided handler in the correct position on the handlers stack,
     * verifying that another handler does not already have the same priority.
     *
     * @param AbstractHandlerType $handler
     *
     * @return $this
     *
     * @throws RuntimeException
     */
    abstract protected function determineStackPosition(AbstractHandlerType $handler);

    /**
     * Each time a new handler is added to the stack, re-determine the active
     * handler by processing them by priority (index value) and checking for the
     * first handler type that is both enabled and supported.
     *
     * @return $this
     */
    abstract protected function determineActiveHandler();
}

/* EOF */
