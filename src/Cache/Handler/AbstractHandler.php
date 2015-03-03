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
    protected $enabled = true;

    /**
     * Set the enabled/disabled state of this cache handler method
     *
     * @param  bool $enabled
     * @return $this
     */
    public function setEnabled($enabled = true)
    {
        $this->enabled = (bool) $enabled;

        return $this;
    }

    /**
     * Check if this cache handler method is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) (true === $this->enabled);
    }
}

/* EOF */
