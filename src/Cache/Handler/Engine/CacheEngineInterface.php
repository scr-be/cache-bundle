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

use Scribe\Component\DependencyInjection\Compiler\CompilerPassHandlerInterface;

/**
 * Interface CacheEngineInterface.
 */
interface CacheEngineInterface extends CompilerPassHandlerInterface
{
    /**
     * The default restriction to check against when adding handler.
     *
     * @var string
     */
    const INTERFACE_NAME_CACHE = __CLASS__;

    /**
     * Handler-specific implementation to determine if the caching method is
     * supported by the current platform.
     *
     * @param mixed ...$by
     *
     * @return bool
     */
    public function isSupported(...$by);

    /**
     * Set the cache handler priority.
     *
     * @param int|null $priority
     *
     * @return $this
     */
    public function setPriority($priority);

    /**
     * Get the cache handler priority.
     *
     * @return int|null
     */
    public function getPriority();

    /**
     * Check if cache handler has a priority.
     *
     * @return bool
     */
    public function hasPriority();

    /**
     * Set the optional closure that determines if this cache handler is supported.
     *
     * @param callable|null $decider
     *
     * @return $this
     */
    public function setSupportedDecider(callable $decider = null);

    /**
     * Un-set the optional closure that determines if this cache handler is supported.
     *
     * @return $this
     */
    public function clearSupportedDecider();

    /**
     * Get the optional closure that determines if this cache handler is supported.
     *
     * @return callable|null
     */
    public function getSupportedDecider();

    /**
     * Type casting object will return its fully-qualified class name.
     *
     * @return string
     */
    public function __toString();
}

/* EOF */
