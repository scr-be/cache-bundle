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

use Scribe\CacheBundle\Cache\Handler\HandlerInterface;

/**
 * Interface HandlerChainInterface
 *
 * @package Scribe\CacheBundle\Cache\Handlers
 */
interface HandlerChainInterface
{
    public function addHandler(HandlerInterface $handler, $priority);
    public function getHandlers();
    public function hasHandlers();
    public function getChosenHandlerName();
}

/* EOF */
