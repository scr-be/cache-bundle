<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <oss@scr.be>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Component\Generator;

/**
 * Interface KeyGeneratorInterface.
 */
interface KeyGeneratorInterface
{
    /**
     * Set the key prefix.
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function setPrefix($prefix);

    /**
     * Get key prefix.
     *
     * @return string
     */
    public function getPrefix();

    /**
     * Set the hash algorithm used to generate key.
     *
     * @param string $algorithm
     *
     * @return $this
     */
    public function setAlgorithm($algorithm);

    /**
     * Get the hash algorithm user to generate key.
     *
     * @return string
     */
    public function getAlgorithm();

    /**
     * Check if key has been compiled and set.
     *
     * @return bool
     */
    public function hasKey();

    /**
     * Get/generate key. If no parameters are provided, previously compiled key returned (if available). If parameters
     * are provided, key is only re-generated when values are different from previous compilation.
     *
     * @param mixed,... $values
     *
     * @return string
     */
    public function getKey(...$values);
}

/* EOF */
