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

/**
 * Class CacheKey
 *
 * @package Scribe\CacheBundle\Utility
 */
class CacheKey implements CacheKeyInterface
{
    /**
     * generate a unique key from a list of argument strings
     *
     * @param  string|null $prefix     optional string prefix to encoded key
     * @param  string $values,... a value to use to create the unique key
     * @return string
     */
    static public function get($prefix = null, ...$values)
    {
        $valuesSize = sizeof($values);
        $value      = '';

        if ($valuesSize === 0) {
            throw new InvalidArgumentException('You cannot generate a cache key from no initial value.');
        }

        for ($i = 0; $i < $valuesSize; $i++) {
            $value .= (string)$values[$i];
        }

        $encodedValue = sha1($value, false);

        if ($prefix !== null) {
            $key = (string)$prefix . '.' . $encodedValue;
        }
        else {
            $key = $encodedValue;
        }

        return $key;
    }
}