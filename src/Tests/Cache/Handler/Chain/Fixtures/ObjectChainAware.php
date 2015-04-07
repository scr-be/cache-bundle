<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Tests\Cache\Handler\Chain\Fixtures;

use Scribe\CacheBundle\Cache\Handler\Chain\HandlerChainAwareTrait;
use Scribe\Utility\UnitTest\AbstractMantleTestCase;

/**
 * Class ObjectChainAware.
 */
class ObjectChainAware extends AbstractMantleTestCase
{
    use HandlerChainAwareTrait;
}

/* EOF */
