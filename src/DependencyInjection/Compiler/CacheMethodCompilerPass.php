<?php
/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class CacheMethodCompilerPass
 *
 * @package Scribe\CacheBundle\DependencyInjection\Compiler
 */

class CacheMethodCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('scribe_cache.method_chain')) {
            return;
        }

        $definition = $container->getDefinition('scribe_cache.method_chain');

        foreach ($container->findTaggedServiceIds('scribe_cache.handler') as $id => $attributes) {
            $definition->addMethodCall(
                'addHandler',
                [ new Reference($id) ]
            );
        }
    }
}

/* EOF */
