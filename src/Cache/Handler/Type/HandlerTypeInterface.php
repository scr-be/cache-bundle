<?php
/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Cache\Handler\Type;

use Scribe\CacheBundle\KeyGenerator\KeyGeneratorInterface;

/**
 * Interface HandlerTypeInterface
 *
 * @package Scribe\CacheBundle\Cache\Handler\Type
 */
interface HandlerTypeInterface
{
    public function __construct(KeyGeneratorInterface $keyGenerator = null, $ttl = 600);
    public function isSupported();
    public function getClassName();
}

/* EOF */
