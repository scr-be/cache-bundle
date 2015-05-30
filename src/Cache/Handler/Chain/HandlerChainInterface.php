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
use Scribe\Component\DependencyInjection\Compiler\CompilerPassChainInterface;

/**
 * Interface CacheCompilerPassChainInterface.
 *
 * @deprecated {@see Scribe\CacheBundle\DependencyInjection\Aware\CacheChainAwareTrait}
 */
interface HandlerChainInterface extends CompilerPassChainInterface
{
    /**
     * Gets the active handler.
     *
     * @return \Scribe\CacheBundle\Cache\Handler\Engine\AbstractCacheEngine
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
