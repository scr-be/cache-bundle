<?php
/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Cache\Chain;

use Scribe\CacheBundle\Cache\Handlers\HandlerInterface;

/**
 * Interface CacheHandlerInterface
 *
 * @package Scribe\CacheBundle\Cache\Chain
 */
class MethodChain
{
    /**
     * An array of available cache handlers sorted by priority via th DI component.
     *
     * @var HandlerInterface[]
     */
    private $handlers = [ ];

    /**
     * Add a cache handler to the stack of available handlers
     *
     * @param HandlerInterface $handler
     */
    public function addHandler(HandlerInterface $handler)
    {
        $this->handlers[ ] = $handler;
    }

    /**
     * Returns the handler from the stack
     */
    public function getHandlers()
    {
        return $this->handlers;
    }
}

/* EOF */
