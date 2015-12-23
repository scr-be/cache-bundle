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
 * Interface CacheManagerInterface.
 */
interface CacheManagerInterface
{
    /**
     * @param bool|false $enabled
     */
    public function __construct($enabled = false);

    /**
     * @param bool $state
     *
     * @return $this
     */
    public function setEnabled($state);

    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * @param CacheCompilerRegistrar $registrar
     *
     * @return $this
     */
    public function setRegistrar(CacheCompilerRegistrar $registrar);

    /**
     * @return CacheAttendantInterface
     */
    public function getActive();

    /**
     * @param int $index
     * 
     * @return $this
     */
    public function setActive($index);

    /**
     * @return $this
     */
    public function determineActive();
}

/* EOF */
