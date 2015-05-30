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

use Scribe\CacheBundle\Cache\Handler\Engine\AbstractCacheEngine;
use Scribe\Component\DependencyInjection\Compiler\CompilerPassHandlerInterface;

/**
 * Class CacheChainMock.
 */
class CacheChainMock extends AbstractCacheChain
{
    /**
     * Provides a fully mocked cache chain. No caching actually occurs, but the full API
     * will function as expected.
     */
    public function __construct()
    {
        parent::__construct(true);
    }

    /**
     * Does nothing but mock the interface.
     *
     * @param null|string|AbstractCacheEngine $forceType
     *
     * @return $this
     */
    protected function determineActiveHandler($forceType = null)
    {
        return $this;
    }

    /**
     * Does nothing but mock the interface.
     *
     * @param CompilerPassHandlerInterface $handler
     *
     * @return $this
     */
    protected function determineStackPosition(CompilerPassHandlerInterface $handler)
    {
        return $this;
    }
}

/* EOF */
