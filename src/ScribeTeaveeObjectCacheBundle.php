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

namespace Scribe\Teavee\ObjectCacheBundle;

use Scribe\Teavee\ObjectCacheBundle\DependencyInjection\Compiler\AttendantCompilerPass;
use Scribe\WonkaBundle\Component\Bundle\AbstractCompilerAwareBundle;

/**
 * Class ScribeTeaveeObjectCacheBundle.
 */
class ScribeTeaveeObjectCacheBundle extends AbstractCompilerAwareBundle
{
    /**
     * @return \Scribe\WonkaBundle\Component\DependencyInjection\Compiler\Pass\AbstractCompilerPass[]
     */
    public function getCompilerPassInstances()
    {
        return [ new AttendantCompilerPass() ];
    }
}

/* EOF */
