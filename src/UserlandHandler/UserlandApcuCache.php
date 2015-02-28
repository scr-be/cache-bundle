<?php
/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\UserlandHandler;

use Scribe\CacheBundle\KeyGenerator\KeyGeneratorTrait;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorInterface;

/**
 * Class UserlandApcuCache
 *
 * @package Scribe\CacheBundle\Component\Caching
 */
class UserlandApcuCache implements UserlandCacheInterface, KeyGeneratorInterface
{
    use KeyGeneratorTrait;

	/**
	 * attempts to retrieve the requested key
     *
	 * @param  string $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		$value = apc_fetch($key, $success);

		if ($success === false) {
			return $default;
		}

		return $value;
	}

	/**
	 * attempts to add a value to the cache
     *
	 * @param  string $key
	 * @param  mixed  $value
	 * @param  int    $ttl
	 * @return bool
	 */
	public function set($key, $value, $ttl = self::TTL_DEFAULT)
	{
		return (boolean)apc_store($key, $value, $ttl);
	}

	/**
	 * checks if the requested key(s) exist in cache
     *
	 * @param  string|array $key
	 * @return bool
	 */
	public function has($key)
	{
		return (boolean)apc_exists($key);
	}
}

/* EOF */