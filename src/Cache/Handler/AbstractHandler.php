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
 * Class AbstractHandler.
 */
abstract class AbstractHandler implements HandlerInterface
{
    /**
     * Allows for enabling/disabling this caching method.
     *
     * @var bool
     */
    protected $enabled = true;

    /**
     * Set the enabled/disabled state of this cache handler method.
     *
     * @param bool $enabled
     *
     * @return $this
     */
    public function setEnabled($enabled = true)
    {
        $this->enabled = (bool) $enabled;

        return $this;
    }

    /**
     * Check if this cache handler method is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) (true === $this->enabled);
    }

    /**
     * Set the time to live for the cache values.
     *
     * @param int $seconds
     *
     * @return $this
     */
    abstract public function setTtl($seconds);

    /**
     * Get the TTL for the cache values.
     *
     * @return int
     */
    abstract public function getTtl();

    /**
     * Set the TTL back to the system default.
     *
     * @return $this
     */
    abstract public function setTtlToDefault();
}

/* EOF */
