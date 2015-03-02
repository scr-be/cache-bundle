<?php
/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Scribe\CacheBundle\DependencyInjection\Compiler\CacheMethodCompilerPass;

/**
 * Class ScribeCacheBundle
 *
 * @package Scribe\CacheBundle
 */
class ScribeCacheBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CacheMethodCompilerPass());
    }
}

/* EOF */
