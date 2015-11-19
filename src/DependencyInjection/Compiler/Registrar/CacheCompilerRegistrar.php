<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\DependencyInjection\Compiler\Registrar;

use Scribe\CacheBundle\Component\Cache\CacheMethodInterface;
use Scribe\WonkaBundle\Component\DependencyInjection\Compiler\Registrar\AbstractCompilerRegistrar;

/**
 * Class CacheCompilerRegistrar.
 */
class CacheCompilerRegistrar extends AbstractCompilerRegistrar
{
    /**
     * {@inheritdoc}
     */
    public function __construct(...$parameters)
    {
        $parameters['interfaceCollection'] = [ CacheMethodInterface::INTERFACE_CACHE_NAME ];

        parent::__construct($parameters);
    }
}

/* EOF */
