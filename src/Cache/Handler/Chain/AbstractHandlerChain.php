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
use Scribe\CacheBundle\Cache\Handler\HandlerInterface;
use Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeInterface;
use Scribe\CacheBundle\Exceptions\RuntimeException;

/**
 * Class AbstractHandler
 *
 * @package Scribe\CacheBundle\Cache\Handlers
 */
abstract class AbstractHandlerChain extends AbstractHandler implements HandlerInterface, HandlerChainInterface
{
    /**
     * An array of available cache handlers sorted by priority via th DI component.
     *
     * @var HandlerTypeInterface[]
     */
    private $handlers = [ ];

    /**
     * An array of handler type to exclude
     *
     * @var string[]
     */
    private $excludedHandlers = [ ];

    /**
     * The handler that is being used for this instance
     *
     * @var HandlerTypeInterface|null
     */
    private $chosenHandler = null;

    /**
     * Setup the object instance properties
     *
     * @param bool  $globalCacheEnabled
     * @param array $excludeHandlers
     */
    public function __construct($globalCacheEnabled = true, $excludeHandlers = [])
    {
        $this
            ->setEnabled($globalCacheEnabled)
            ->setExcludedHandlers($excludeHandlers)
        ;
    }

    /**
     * Add a cache handler to the stack of available handlers
     *
     * @param HandlerInterface $handler
     */
    public function addHandler(HandlerInterface $handler, $priority)
    {
        $this->handlers[ $priority ] = $handler;
    }

    /**
     * Returns the handler from the stack
     *
     * @return array
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * Check if any handlers exist
     *
     * @return bool
     */
    public function hasHandlers()
    {
        return (bool) (true === (count($this->handlers)) > 0);
    }

    /**
     * Sets the chosen handler
     *
     * @param  HandlerTypeInterface $handler
     * @return $this
     */
    protected function setChosenHandler(HandlerTypeInterface $handler)
    {
        $this->chosenHandler = $handler;

        return $this;
    }

    /**
     * Gets the chosen handler
     *
     * @return HandlerTypeInterface|null
     */
    protected function getChosenHandler()
    {
        return $this->chosenHandler;
    }

    /**
     * Checks if a chosen handler has been set
     *
     * @return bool
     */
    protected function hasChosenHandler()
    {
        return (bool) (null !== $this->chosenHandler);
    }

    /**
     * getChosenHandlerName
     *
     * @return string
     * @throws RuntimeException
     */
    public function getChosenHandlerName()
    {
        return (string) $this
            ->getFirstAvailableHandler()
            ->getClassName()
        ;
    }

    /**
     * Sets the handler types that are excluded
     *
     * @param  array $handlers
     * @return $this
     */
    protected function setExcludedHandlers(array $handlers = [ ])
    {
        $this->excludedHandlers = $handlers;

        return $this;
    }

    /**
     * Gets the list of excluded handlers
     *
     * @return array
     */
    protected function getExcludedHandlers()
    {
        return $this->excludedHandlers;
    }

    /**
     * Returns the first supported and non-excluded handler type
     *
     * @param  HandlerTypeInterface $handler
     * @return bool
     */
    protected function isExcludedHandler(HandlerTypeInterface $handler)
    {
        if (true === in_array($handler->getClassName(), $this->getExcludedHandlers())) {
            return true;
        }

        return false;
    }

    /**
     * Gets the first supported handler
     *
     * @return HandlerTypeInterface
     * @throws RuntimeException
     */
    protected function getFirstAvailableHandler()
    {
        if (true === $this->hasChosenHandler()) {

            return $this->getChosenHandler();
        }

        foreach ($this->getHandlers() as $handler) {
            if (true === $handler->isSupported() &&
                false === $this->isExcludedHandler($handler))
            {
                $this->setChosenHandler($handler);

                return $handler;
            }
        }

        throw new RuntimeException(
            'No valid cache handler found.'
        );
    }

    /**
     * Set the time to live for the cache values
     *
     * @param  int $seconds
     * @return $this
     */
    public function setTtl($seconds)
    {
        foreach ($this->getHandlers() as $h) {
            $h->setTtl($seconds);
        }

        return $this;
    }

    /**
     * Get the TTL for the cache values
     *
     * @return int
     */
    public function getTtl()
    {
        return (int) $this->getFirstAvailableHandler()->getTtl();
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
            ->getFirstAvailableHandler()
            ->setKey(...$values)
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
        return (string) $this
            ->getFirstAvailableHandler()
            ->getKey()
        ;
    }

    /**
     * Check if a key has been setup
     *
     * @return bool
     */
    public function hasKey()
    {
        return (bool) (true === $this->getFirstAvailableHandler()->hasKey());
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
        return $this
            ->getFirstAvailableHandler()
            ->get(...$keyValues)
        ;
    }

    /**
     * Set a cached value; will overwrite a value with the same key silently
     *
     * @param  string|int|object|callable $data
     * @param  ...mixed                   $keyValues
     * @return true
     */
    public function set($data, ...$keyValues)
    {
        $this
            ->getFirstAvailableHandler()
            ->set($data, ...$keyValues)
        ;

        return $this;
    }

    /**
     * Check for non-stale existence of cached value with same key
     *
     * @param ...$keyValues
     * @return bool
     */
    public function has(...$keyValues)
    {
        return (bool) $this
            ->getFirstAvailableHandler()
            ->has(...$keyValues)
        ;
    }
}

/* EOF */
