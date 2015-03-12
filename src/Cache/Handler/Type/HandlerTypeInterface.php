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

use Scribe\CacheBundle\KeyGenerator\KeyGeneratorInterface;

/**
 * Interface HandlerTypeInterface
 *
 * @package Scribe\CacheBundle\Cache\Handler\Type
 */
interface HandlerTypeInterface
{
    /**
     * Setup the class instance with the required properties
     *
     * @param KeyGeneratorInterface|null $keyGenerator
     * @param int                        $ttl
     * @param int|null                   $priority
     * @param bool                       $disabled
     * @param callable|null              $supportedDecider
     */
    public function __construct(KeyGeneratorInterface $keyGenerator = null, $ttl = 1800, $priority = null, $disabled = false, callable $supportedDecider = null);

    /**
     * Handler-specific implementation to determine if the caching method is
     * supported by the current platform
     *
     * @return bool
     */
    public function isSupported();

    /**
     * Set the cache handler priority
     *
     * @param  int|null $priority
     * @return $this
     */
    public function setPriority($priority);

    /**
     * Get the cache handler priority
     *
     * @return int|null
     */
    public function getPriority();

    /**
     * Check if cache handler has a priority
     *
     * @return bool
     */
    public function hasPriority();

    /**
     * Set the optional closure that determines if this cache handler is supported
     *
     * @param  callable|null $decider
     * @return $this
     */
    public function setSupportedDecider(callable $decider = null);

    /**
     * Un-set the optional closure that determines if this cache handler is supported
     *
     * @return $this
     */
    public function unsetSupportedDecider();

    /**
     * Get the optional closure that determines if this cache handler is supported
     *
     * @return callable|null
     */
    public function getSupportedDecider();

    /**
     * Get the handler type
     *
     * @return string
     */
    public function getType();

    /**
     * Type casting object will return its fully-qualified class name
     *
     * @return string
     */
    public function __toString();
}

/* EOF */
