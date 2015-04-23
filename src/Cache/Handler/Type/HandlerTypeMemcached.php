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

use Scribe\CacheBundle\Exceptions\RuntimeException;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorInterface;
use Memcached;

/**
 * Class HandlerTypeMemcached.
 */
class HandlerTypeMemcached extends AbstractHandlerType
{
    /**
     * Available memcached option type strings to their object constant.
     *
     * @var array
     */
    protected $optionTypes = [
        'serializer'           => Memcached::OPT_SERIALIZER,
        'libketama_compatible' => Memcached::OPT_LIBKETAMA_COMPATIBLE,
        'io_no_block'          => Memcached::OPT_NO_BLOCK,
        'tcp_no_delay'         => Memcached::OPT_TCP_NODELAY,
        'compression'          => Memcached::OPT_COMPRESSION,
        'compression_method'   => Memcached::OPT_COMPRESSION_TYPE,
    ];

    /**
     * Available memcached option type value strings to their object constant.
     *
     * @var array
     */
    protected $optionValues = [
        'serializer' => [
            'igbinary' => Memcached::SERIALIZER_IGBINARY,
            'json'     => Memcached::SERIALIZER_JSON,
            'php'      => Memcached::SERIALIZER_PHP,
        ],
        'compression_method'  => [
            'zlib'   => Memcached::COMPRESSION_ZLIB,
            'fastlz' => Memcached::COMPRESSION_FASTLZ,
        ],
    ];

    /**
     * Our memcached object instance.
     *
     * @var Memcached
     */
    protected $memcached;

    /**
     * Setup the class instance with the required properties.
     *
     * @param KeyGeneratorInterface|null $keyGenerator
     * @param int                        $ttl
     * @param int|null                   $priority
     * @param bool                       $disabled
     * @param callable|null              $supportedDecider
     */
    public function __construct(KeyGeneratorInterface $keyGenerator = null, $ttl = 1800, $priority = null, $disabled = false, callable $supportedDecider = null)
    {
        parent::__construct($keyGenerator, $ttl, $priority, $disabled, $supportedDecider);

        if (false === $this->isSupported()) {
            return;
        }

        $this->memcached = new Memcached();
    }

    /**
     * An array of option definitions as passed by the DI compiler pass.
     *
     * @param array $options
     */
    public function setOptions(array $options = [])
    {
        if (true !== $this->isSupported()) {
            return;
        }

        $resolvedOptions = [];

        foreach ($options as $type => $value) {
            $this->handleOptionResolution($resolvedOptions, $type, $value);
        }

        $this->memcached->setOptions($resolvedOptions);
    }

    /**
     * Resolve the Memcached constants for setOptions based on the provided human-readable config.
     *
     * @param array  $options
     * @param string $type
     * @param mixed  $value
     */
    protected function handleOptionResolution(array &$options, $type, $value)
    {
        $options[ $this->handleOptionTypeResolution($type) ] =
            $this->handleOptionValueResolution($type, $value);
    }

    /**
     * Determine the Memcached option type constant that should be returned.
     *
     * @param string $type
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    protected function handleOptionTypeResolution($type)
    {
        if (false === array_key_exists($type, $this->optionTypes)) {
            throw new RuntimeException(
                sprintf('Unknown memcached option type %s specified.', $type)
            );
        }

        return $this->optionTypes[ $type ];
    }

    /**
     * Determine the Memcached option value constant that should be returned.
     *
     * @param string          $type
     * @param bool|int|string $value
     *
     * @return mixed
     */
    protected function handleOptionValueResolution($type, $value)
    {
        if (true === array_key_exists($type, $this->optionValues) &&
            true === array_key_exists($value, $this->optionValues[ $type ])) {
            return $this->optionValues[ $type ][ $value ];
        }

        return $value;
    }

    /**
     * Array of server definitions to set (generally passed by Symfony's DI).
     * This will also reset any previously configured servers.
     *
     * @param array $servers
     */
    public function setServers(array $servers = [])
    {
        if (true === $this->isSupported()) {
            $this->memcached->resetServerList();
        }

        $this->addServers($servers);
    }

    /**
     * Array of server definitions to add. Unlike {@see:setServers} this method
     * does not reset previously configured servers. Be careful about adding
     * duplicate entries, as no check is made to disallow such.
     *
     * @param array $servers
     *
     * @throws RuntimeException
     */
    public function addServers(array $servers = [])
    {
        if (true !== $this->isSupported()) {
            return;
        }

        $resolvedServers = [];
        foreach ($servers as $name => $parameters) {
            $this->handleServerResolution($resolvedServers, $name, $parameters);
        }

        $this->memcached->addServers($resolvedServers);
    }

    /**
     * Resolve single-server configuration array.
     *
     * @param array  $resolvedServers
     * @param string $name
     * @param array  $parameters
     *
     * @throws RuntimeException
     */
    protected function handleServerResolution(array &$resolvedServers, $name, array $parameters)
    {
        if (false === (count($parameters) === 3)) {
            throw new RuntimeException(
                'Unknown number of server connection parameters. Please provide 3: ip/host, port, and weight.'
            );
        }

        $resolvedServers[ $name ] = array_values($parameters);
    }

    /**
     * Check if the handler type is supported by the current environment.
     *
     * @return bool
     */
    public function isSupported(...$by)
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
     * @param int           $expectedCode
     * @param callable|null $decider
     *
     * @return bool
     */
    protected function isLastActionSuccessful($expectedCode = Memcached::RES_SUCCESS, callable $decider = null)
    {
        if (null === $decider) {
            $decider = function ($expected, $received) {
                return (bool) ($received > $expected ? false : true);
            };
        }

        $receivedCode = $this->memcached->getResultCode();

        return (bool) $decider($expectedCode, $receivedCode);
    }

    /**
     * Retrieve the cached data using the provided key.
     *
     * @param string $key
     *
     * @return string|null
     */
    protected function getUsingHandler($key)
    {
        $data = $this->memcached->get($key);

        if (true === $this->isLastActionSuccessful()) {
            return $data;
        }

        return;
    }

    /**
     * Set the cached data using the key (overwriting data that may exist already).
     *
     * @param string $data
     * @param string $key
     *
     * @return bool
     */
    protected function setUsingHandler($data, $key)
    {
        $this->memcached->set($key, $data, $this->getTtl());

        return $this->isLastActionSuccessful();
    }

    /**
     * Check if the cached data exists using the provided key.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function hasUsingHandler($key)
    {
        return (bool) (null !== $this->getUsingHandler($key));
    }

    /**
     * Delete the cached data using the provided key.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function delUsingHandler($key)
    {
        $this->memcached->delete($key, 0);

        return $this->isLastActionSuccessful();
    }

    /**
     * Flush all cached data within this cache mechanism-type.
     *
     * @return bool
     */
    protected function flushAllUsingHandler()
    {
        $this->memcached->flush();

        return $this->isLastActionSuccessful();
    }
}

/* EOF */
