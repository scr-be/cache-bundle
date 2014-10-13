<?php
/*
 * This file is part of the Scribe World Application.
 *
 * (c) Scribe Inc. <scribe@scribenet.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Utility;

use InvalidArgumentException;

/**
 * Class interface CacheKeyInterface
 *
 * @package Scribe\CacheBundle\Utility
 */
interface CacheKeyInterface
{
    /**
     * generate a unique key from a list of argument strings
     *
     * @param  string|null $prefix     optional string prefix to encoded key
     * @param  string      $values,... a value to use to create the unique key
     * @return string
     */
    static public function get($prefix = null, ...$values);
}