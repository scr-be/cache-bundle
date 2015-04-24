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
use Scribe\CacheBundle\Cache\Handler\Type\HandlerTypeInterface;
use Scribe\CacheBundle\Exceptions\RuntimeException;
use Scribe\Component\DependencyInjection\Compiler\CompilerPassHandlerInterface;

/**
 * Class HandlerChain.
 */
class HandlerChain extends AbstractHandlerChain
{
    /**
     * Stack the provided handler in the correct position on the handlers stack,
     * verifying that another handler does not already have the same priority.
     *
     * @param HandlerTypeInterface $handler
     *
     * @return $this
     *
     * @throws RuntimeException
     */
    protected function determineStackPosition(HandlerTypeInterface $handler)
    {
        static $internalHandlerPriority = 100;

        if (null === ($handlerPriority = $handler->getPriority())) {
            $handlerPriority = $internalHandlerPriority++;
        }

        if (true === array_key_exists($handlerPriority, $this->handlers)) {
            throw new RuntimeException(sprintf(
               'A duplicate priority of %d cannot be set for %s. Please review your config.',
               $handlerPriority,
               $handler->getType()
           ));
        }

        $this->handlers[$handlerPriority] = $handler;

        return $this;
    }

    /**
     * Each time a new handler is added to the stack, re-determine the active
     * handler by processing them by priority (index value) and checking for the
     * first handler type that is both enabled and supported.
     *
     * @param null|string|AbstractHandlerType $forceSelection
     *
     * @return $this
     */
    protected function determineActiveHandler($forceSelection = null)
    {
        ksort($this->handlers);

        if (null === $forceSelection) {
            return $this->determineActiveHandlerAutomatic();
        }

        return $this->determineActiveHandlerForced($forceSelection);
    }

    /**
     * @return $this
     */
    protected function determineActiveHandlerAutomatic()
    {
        $chosenHandler = null;

        foreach ($this->getHandlerCollection() as $handler) {
            if (true === $handler->isEnabled() &&
                true === $handler->isSupported()) {
                $chosenHandler = $handler;
                break;
            }
        }

        if ($chosenHandler instanceof AbstractHandlerType) {
            $this->setActiveHandler($chosenHandler);
        } else {
            $this->unsetActiveHandlerType();
        }

        return $this;
    }

    /**
     * @param null|string|AbstractHandlerType $forceSelection
     *
     * @throws RuntimeException
     *
     * @return $this
     */
    protected function determineActiveHandlerForced($forceSelection)
    {
        $chosenHandler = null;
        $forceSelection = $forceSelection instanceof AbstractHandlerType ?
            strtolower($forceSelection->getType()) : strtolower($forceSelection);

        foreach ($this->handlers as $handler) {
            if ($forceSelection === $handler->getType() &&
                true === $handler->isSupported()) {
                $chosenHandler = $handler;
                break;
            }

            continue;
        }

        if (false === ($chosenHandler instanceof AbstractHandlerType)) {
            throw new RuntimeException(
                sprintf(
                    'Could not find requested cache handler type "%s".',
                    $forceSelection
                )
            );
        }

        if (null !== $chosenHandler) {
            $this->setActiveHandler($chosenHandler);
        }

        return $this;
    }
}

/* EOF */
