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
 * UserlandCacheInterface
 * Simple userland cachine mechanism interface
 */
interface UserlandCacheInterface {

	/**
	 * default time (in seconds) to store cached values
	 */
	const TTL_DEFAULT = 6000;

	/**
	 * attempts to retrieve the requested key
	 * @param  string $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	public function get($key, $default = null);

	/**
	 * attempts to add a value to the cache
	 * @param  string $key
	 * @param  mixed  $value
	 * @param  int    $ttl
	 * @return bool
	 */
	public function set($key, $value, $ttl = self::TTL_DEFAULT);

	/**
	 * checks if the requested key exist in cache
	 * @param  string $key
	 * @return bool
	 */
	public function has($key);
}