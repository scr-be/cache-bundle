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

use PHPUnit_Framework_TestCase;
use Scribe\CacheBundle\Cache\Handler\Chain\HandlerChainAwareTrait;

/**
 * Class ObjectChainAware.
 */
class ObjectChainAware extends PHPUnit_Framework_TestCase
{
    use HandlerChainAwareTrait;
}

/* EOF */
