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

use Scribe\CacheBundle\Cache\Handler\Type\AbstractHandlerType;

/**
 * Interface HandlerChainInterface.
 */
interface HandlerChainInterface
{
    /**
     * Setup the object instance properties.
     *
     * @param bool $disabled
     */
    public function __construct($disabled = false);

    /**
     * Add a cache handler type to the stack of tagged handlers.
     *
     * @param AbstractHandlerType $handler
     */
    public function addHandler(AbstractHandlerType $handler);

    /**
     * Sets an array of handlers (clearing any previous ones).
     *
     * @param AbstractHandlerType[] $handlers
     *
     * @return $this
     */
    public function setHandlers(array $handlers = [ ]);

    /**
     * Returns the handler from the stack.
     *
     * @return AbstractHandlerType[]
     */
    public function getHandlers();

    /**
     * Check if any handlers have been registered.
     *
     * @return bool
     */
    public function hasHandlers();

    /**
     * Gets the active handler.
     *
     * @return AbstractHandlerType
     */
    public function getActiveHandler();

    /**
     * Checks if an active handler has been set.
     *
     * @return bool
     */
    public function hasActiveHandler();

    /**
     * Get the active handler type, by default the short name of class such as
     * simply "apcu" but optionally return the fully-qualified class name.
     *
     * @param bool $fullyQualified
     *
     * @return string
     */
    public function getActiveHandlerType($fullyQualified = false);
}

/* EOF */
