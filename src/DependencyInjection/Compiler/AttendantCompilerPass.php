<?php

/*
 * This file is part of the Teavee Block Manager Bundle.
 *
 * (c) Scribe Inc.     <oss@scr.be>
 * (c) Rob Frawley 2nd <rmf@scr.be>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\Teavee\ObjectCacheBundle\DependencyInjection\Compiler;

use Scribe\WonkaBundle\Component\DependencyInjection\Compiler\Pass\AbstractCompilerPass;

/**
 * Class AttendantCompilerPass.
 */
class AttendantCompilerPass extends AbstractCompilerPass
{
    /**
     * @return string
     */
    public function getRegistrarSrvName()
    {
        return 's.teavee_object_cache.registrar';
    }

    /**
     * @return string
     */
    public function getAttendantTagName()
    {
        return 's.teavee_object_cache.attendant';
    }
}

/* EOF */
