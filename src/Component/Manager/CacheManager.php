<?php

/*
 * This file is part of the Teavee Object Caching Bundle.
 *
 * (c) Scribe Inc.     <oss@scr.be>
 * (c) Rob Frawley 2nd <rmf@scr.be>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\Teavee\ObjectCacheBundle\Component\Manager;

use Scribe\Teavee\ObjectCacheBundle\Component\Cache\CacheAttendantInterface;
use Scribe\Teavee\ObjectCacheBundle\DependencyInjection\Compiler\Registrar\CacheCompilerRegistrar;

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
     * @var CacheAttendantInterface
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
        $this->determineActive();

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
        $this->determineActive();

        return $this;
    }

    /**
     * @param int $index
     *
     * @return $this
     */
    public function setActive($index)
    {
        if (null !== ($active = getArrayElement($index, $this->registrar->getAttendantCollection()))) {
            $this->active = $active;

            return true;
        }

        return false;
    }

    /**
     * @return CacheAttendantInterface
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @return CacheAttendantInterface|null
     */
    public function determineActive()
    {
        $this->active = null;

        if (!$this->isEnabled()) {
            return;
        }

        foreach ($this->registrar as $attendant) {
            if ($attendant->isEnabled() && $attendant->isSupported()) {
                $this->active = $attendant;
                break;
            }
        }

        return $this->active;
    }
}

/* EOF */
