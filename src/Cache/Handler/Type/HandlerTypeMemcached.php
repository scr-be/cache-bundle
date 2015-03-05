<?php
/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Cache\Handler\Type;

use Scribe\CacheBundle\KeyGenerator\KeyGeneratorInterface;
use Memcached;

/**
 * Class HandlerTypeMemcached
 *
 * @package Scribe\CacheBundle\Cache\Handler\Type
 */
class HandlerTypeMemcached extends AbstractHandlerType
{
    /**
     * Setup the class instance with the required properties
     *
     * @param KeyGeneratorInterface $keyGenerator
     * @param int                   $ttl
     * @param int|null              $priority
     * @param bool                  $disabled
     * @param callable              $supportedDecider
     */
    public function __construct(KeyGeneratorInterface $keyGenerator = null, $ttl = 1800, $priority = null, $disabled = false, callable $supportedDecider = null)
    {
        parent::__construct($keyGenerator, $ttl, $priority, $disabled, $supportedDecider);

        if (false === $this->isSupported()) {
            return;
        }

        $this->memcached = new Memcached;
    }

    /**
     * An array of option definitions as passed by the DI compiler pass
     *
     * @param array $options
     */
    public function setOptions(array $options = [ ])
    {
        if (true !== $this->isSupported()) {
            return;
        }

        $setOpts = [ ];
        foreach ($options as $o => $v) {
            if ($o === 'serializer') {
                if ($v === 'igbinary') {
                    $setOpts[ Memcached::OPT_SERIALIZER ] = Memcached::SERIALIZER_IGBINARY;
                }
                else if ($v === 'json') {
                    $setOpts[ Memcached::OPT_SERIALIZER ] = Memcached::SERIALIZER_JSON;
                }
                else {
                    $setOpts[ Memcached::OPT_SERIALIZER ] = Memcached::SERIALIZER_PHP;
                }
            }
            else if ($o === 'libketama_compatible') {
                $setOpts[ Memcached::OPT_LIBKETAMA_COMPATIBLE ] = (bool) $v;
            }
            else if ($o === 'io_no_block') {
                $setOpts[ Memcached::OPT_NO_BLOCK ] = (bool) $v;
            }
            else if ($o === 'tcp_no_delay') {
                $setOpts[ Memcached::OPT_TCP_NODELAY ] = (bool) $v;
            }
            else if ($o === 'compression') {
                $setOpts[ Memcached::OPT_COMPRESSION ] = (bool) $v;
            }
            else if ($o === 'compression_method') {
                if ($v === 'zlib') {
                    $setOpts[ Memcached::OPT_COMPRESSION_TYPE ] = Memcached::COMPRESSION_ZLIB;
                }
                else {
                    $setOpts[ Memcached::OPT_COMPRESSION_TYPE ] = Memcached::COMPRESSION_FASTLZ;
                }
            }
        }

        $this->memcached->setOptions($setOpts);
    }

    /**
     * Array of server definitions as passed by the DI compiler pass
     *
     * @param array $servers
     */
    public function setServers(array $servers = [ ])
    {
        if (true !== $this->isSupported()) {
            return;
        }

        $setOpts = [ ];
        foreach ($servers as $n => $s) {
            $setOpts[ ] = array_values($s);
        }

        $this->memcached->resetServerList();
        $this->memcached->addServers($setOpts);
    }

    /**
     * Check if the handler type is supported by the current environment
     *
     * @return bool
     */
    public function isSupported()
    {
        if (null !== ($decision = $this->callSupportedDecider())) {

            return (bool) $decision;
        }

        return (bool) (true === extension_loaded('memcached'));
    }

    /**
     * Get the result of the last operation based on a comparison of the expected
     * return code and the received return code via an optionally custom callable.
     *
     * @param  int      $expectedCode
     * @param  callable $decider
     * @return bool
     */
    protected function getDecisionOnLastActionSuccess($expectedCode = Memcached::RES_SUCCESS, callable $decider = null)
    {
        if (null === $decider) {
            $decider = function($expected, $received) {
                return (bool) ($received > $expected ? false : true);
            };
        }

        $receivedCode = $this->memcached->getResultCode();

        return (bool) $decider($expectedCode, $receivedCode);
    }

    /**
     * Retrieve the cached data using the provided key
     *
     * @param  string $key
     * @return string|null
     */
    protected function getUsingHandler($key)
    {
        $data = $this->memcached->get($key);

        if (true === $this->getDecisionOnLastActionSuccess()) {
            return $data;
        }

        return null;
    }

    /**
     * Set the cached data using the key (overwriting data that may exist already)
     *
     * @param  string $data
     * @param  string $key
     * @return bool
     */
    protected function setUsingHandler($data, $key)
    {
        $this->memcached->set($key, $data, $this->getTtl());

        return $this->getDecisionOnLastActionSuccess();
    }

    /**
     * Check if the cached data exists using the provided key
     *
     * @param  string $key
     * @return bool
     */
    protected function hasUsingHandler($key)
    {
        if (null === $this->getUsingHandler($key)) {

            return false;
        }

        return true;
    }

    /**
     * Delete the cached data using the provided key
     *
     * @param  string $key
     * @return bool
     */
    protected function delUsingHandler($key)
    {
        $this->memcached->delete($key, 0);

        return $this->getDecisionOnLastActionSuccess();
    }

    /**
     * Flush all cached data within this cache mechanism-type
     *
     * @return bool
     */
    protected function flushAllUsingHandler()
    {
        $this->memcached->flush();

        return $this->getDecisionOnLastActionSuccess();
    }
}

/* EOF */
