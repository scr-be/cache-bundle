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

class CacheHandlerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('scribe_cache.handler_chain')) {
            return;
        }

        $definition = $container->getDefinition('scribe_cache.handler_chain');

        $unset_priority = 100;
        foreach ($container->findTaggedServiceIds('scribe_cache.handler_type') as $id => $attributes) {
            if (false === ($priority = array_search('default_priority', $attributes))) {
                $priority = $unset_priority++;
            }

            $definition->addMethodCall(
                'addHandler',
                [ new Reference($id), $priority ]
            );
        }
    }
}

/* EOF */
