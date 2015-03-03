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
 * Interface HandlerChainInterface
 *
 * @package Scribe\CacheBundle\Cache\Handlers
 */
interface HandlerChainInterface
{
    public function addHandler(AbstractHandlerType $handler);
    public function setHandlers(array $handlers = [ ]);
    public function getHandlers();
    public function hasHandlers();
    public function getActiveHandler();
    public function hasActiveHandler();
    public function getActiveHandlerType($fullyQualified = false);
}

/* EOF */
