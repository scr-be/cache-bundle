<?php
/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Cache\Handlers;

use Scribe\CacheBundle\KeyGenerator\KeyGeneratorInterface;

/**
 * Interface HandlerInterface
 *
 * @package Scribe\CacheBundle\Cache\Handlers
 */
interface HandlerInterface
{
    public function __construct(KeyGeneratorInterface $keyGenerator = null);
    public function isSupported();
    public function setEnabled($cacheEnabled = true);
    public function isEnabled();
    public function isDisabled();
    public function setKey(...$keyValues);
    public function getKey();
    public function hasKey();
    public function get(...$keyValues);
    public function set($data, ...$keyValues);
    public function has(...$keyValues);
    public function del(...$keyValues);
}

/* EOF */
