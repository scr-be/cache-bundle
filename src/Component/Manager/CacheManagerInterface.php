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
     * @return CacheMethodInterface
     */
    public function getActive();

    /**
     * @return $this
     */
    public function setActive();
}

/* EOF */
