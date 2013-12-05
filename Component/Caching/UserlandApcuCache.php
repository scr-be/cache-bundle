<?php
/*
 * This file is part of the Scribe World Application.
 *
 * (c) Scribe Inc. <scribe@scribenet.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Component\Caching;

/**
 * UserlandApcuCache
 * Simple 
 */
class UserlandApcuCache implements UserlandCacheInterface {

	/**
	 * attempts to retrieve the requested key
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
	 * @param  string|array $key
	 * @return bool
	 */
	public function has($key)
	{
		return (boolean)apc_exists($key);
	}
}