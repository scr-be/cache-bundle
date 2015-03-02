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

use Scribe\CacheBundle\Exceptions\InvalidArgumentException;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorAwareTrait;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorInterface;

/**
 * Class AbstractHandler
 *
 * @package Scribe\CacheBundle\Cache\Handler
 */
abstract class AbstractHandler implements HandlerInterface
{
    /**
     * Allows for enabling/disabling this caching method
     *
     * @var bool
     */
    protected $cacheEnabled = true;

    /**
     * Set the enabled/disabled state of this cache handler method
     *
     * @param  bool $cacheEnabled
     * @return $this
     */
    public function setEnabled($cacheEnabled = true)
    {
        $this->cacheEnabled = (bool) $cacheEnabled;

        return $this;
    }

    /**
     * Check if this cache handler method is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) (true === $this->cacheEnabled);
    }

    /**
     * Check if this cache handler method is enabled
     *
     * @return bool
     */
    public function isDisabled()
    {
        return (bool) (false === $this->cacheEnabled);
    }
}

/* EOF */
