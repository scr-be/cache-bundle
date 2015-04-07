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
 * Class CacheMethodCompilerPass.
 */
class CacheHandlerCompilerPass implements CompilerPassInterface
{
    /**
     * Process the bundle's container. Perform compiler pass to provide cache chain
     * handler with all services tagged as handler types.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (true === $container->hasDefinition('s.cache.handler_chain')) {
            $chainDefinition = $container->getDefinition(
                's.cache.handler_chain'
            );
            $handlerDefinitions = $container->findTaggedServiceIds(
                's.cache.handler_type'
            );

            foreach ($handlerDefinitions as $id => $attributes) {
                $chainDefinition->addMethodCall(
                    'addHandler',
                    [new Reference($id)]
                );
            }
        }
    }
}

/* EOF */
