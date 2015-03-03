<?php
/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Cache\Handler;

/**
 * Interface HandlerInterface
 *
 * @package Scribe\CacheBundle\Cache\Handler
 */
interface HandlerInterface
{
    public function setEnabled($cacheEnabled = true);
    public function isEnabled();
    public function setKey(...$keyValues);
    public function get(...$keyValues);
    public function set($data, ...$keyValues);
    public function has(...$keyValues);
    public function del(...$keyValues);
    public function flushAll();
}

/* EOF */
