<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <oss@scr.be>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\DependencyInjection\Compiler\Pass;

use Scribe\WonkaBundle\Component\DependencyInjection\Compiler\Pass\AbstractCompilerPass;

/**
 * Class CacheCompilerPass.
 */
class CacheCompilerPass extends AbstractCompilerPass
{
    /**
     * @return string
     */
    public function getRegistrarSrvName()
    {
        return 's.cache.registrar';
    }

    /**
     * @return string
     */
    public function getAttendantTagName()
    {
        return 's.cache.attendant';
    }
}

/* EOF */
