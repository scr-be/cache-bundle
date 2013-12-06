<?php
/*
 * This file is part of the Scribe World Application.
 *
 * (c) Scribe Inc. <scribe@scribenet.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Templating\Extension;

use Scribe\CacheBundle\Component\Caching\UserlandCacheInterface;
use Twig_Extension,
    Twig_SimpleFunction;

/**
 * UserlandCacheExtension
 */
class UserlandCacheExtension extends Twig_Extension 
{
    /**
     * @var UserlandCacheInterface
     */
    private $cache = null;

    /**
     * constructor provides the caching instance
     * @param UserlandCacheInterface $cache
     */
    public function __construct(UserlandCacheInterface $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * @param  string $key
     * @return mixed
     */
    public function getCache($key, $default = null)
    {
        return $this->cache->get($key, $default);
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setCache($key, $value, $ttl = UserlandCacheInterface::TTL_DEFAULT)
    {
        return $this->cache->set($key, $value, $ttl);
    }

    /**
     * set the available functions
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('get_cache', [$this, 'getCache'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('set_cache', [$this, 'setCache'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * return the class name
     * @return string
     */
    public function getName()
    {
        return __CLASS__;
    }
}