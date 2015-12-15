<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <oss@scr.be>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Component\Manager;

use Scribe\CacheBundle\Component\Cache\CacheMethodInterface;
use Scribe\CacheBundle\DependencyInjection\Compiler\Registrar\CacheCompilerRegistrar;

/**
 * Class CacheManager.
 */
class CacheManager implements CacheManagerInterface
{
    /**
     * @var bool
     */
    protected $enabled = false;

    /**
     * @var CacheMethodInterface
     */
    protected $active;

    /**
     * @var CacheCompilerRegistrar
     */
    protected $registrar;

    /**
     * @param bool|false $enabled
     */
    public function __construct($enabled = false)
    {
        $this->enabled = (bool) $enabled;
    }

    /**
     * @param bool $state
     *
     * @return $this
     */
    public function setEnabled($state)
    {
        $this->enabled = (bool) $state;
        $this->setActive();

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->enabled;
    }

    /**
     * @param CacheCompilerRegistrar $registrar
     *
     * @return $this
     */
    public function setRegistrar(CacheCompilerRegistrar $registrar)
    {
        $this->registrar = $registrar;
        $this->setActive();

        return $this;
    }

    /**
     * @return CacheMethodInterface
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @return $this
     */
    public function setActive()
    {
        $this->active = null;

        if (!$this->isEnabled()) {
            return $this;
        }

        foreach ($this->registrar as $attendant) {
            if (!$attendant->isEnabled() || !$attendant->isSupported()) {
                continue;
            }

            $this->active = $attendant;
            break;
        }

        return $this;
    }
}

/* EOF */
