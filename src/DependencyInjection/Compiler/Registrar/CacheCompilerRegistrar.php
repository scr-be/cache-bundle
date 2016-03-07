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

namespace Scribe\Teavee\ObjectCacheBundle\DependencyInjection\Compiler\Registrar;

use Scribe\Teavee\ObjectCacheBundle\Component\Cache\CacheAttendantInterface;
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
        $parameters['interfaceCollection'] = [CacheAttendantInterface::CACHE_ATTENDANT_INTERFACE_FQCN];

        parent::__construct($parameters);
    }
}

/* EOF */
