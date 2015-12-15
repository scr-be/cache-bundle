<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <oss@scr.be>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle;

use Scribe\CacheBundle\DependencyInjection\Compiler\Pass\CacheCompilerPass;
use Scribe\WonkaBundle\Component\Bundle\AbstractCompilerAwareBundle;

/**
 * Class ScribeCacheBundle.
 */
class ScribeCacheBundle extends AbstractCompilerAwareBundle
{
    /**
     * @return array
     */
    public function getCompilerPassInstances()
    {
        return [
            new CacheCompilerPass(),
        ];
    }
}

/* EOF */
