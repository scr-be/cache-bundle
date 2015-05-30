<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Cache\Handler\Engine;

use Memcached;
use Scribe\CacheBundle\Exceptions\RuntimeException;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorInterface;
use Scribe\Utility\Extension;

/**
 * Class CacheEngineMemcached.
 */
class CacheEngineMemcached extends AbstractCacheEngine
{
    /**
     * The return values memcached may return for success.
     *
     * @var string[]
     */
    protected static $memcachedRetSuccess = [
        0 => 'MEMCACHED_SUCCESS',
        12 => 'MEMCACHED_DATA_EXISTS',
        15 => 'MEMCACHED_STORED',
        22 => 'MEMCACHED_DELETED',
        23 => 'MEMCACHED_VALUE',
        24 => 'MEMCACHED_STAT',
        25 => 'MEMCACHED_ITEM',
        32 => 'MEMCACHED_BUFFERED',
    ];

    /**
     * The return values memcached may return for errors.
     *
     * @var string[]
     */
    protected static $memcachedRetFailure = [
        1 => 'MEMCACHED_FAILURE',
        2 => 'MEMCACHED_HOST_LOOKUP_FAILURE',
        3 => 'MEMCACHED_CONNECTION_FAILURE',
        4 => 'MEMCACHED_CONNECTION_BIND_FAILURE',
        5 => 'MEMCACHED_WRITE_FAILURE',
        6 => 'MEMCACHED_READ_FAILURE',
        7 => 'MEMCACHED_UNKNOWN_READ_FAILURE',
        8 => 'MEMCACHED_PROTOCOL_ERROR',
        9 => 'MEMCACHED_CLIENT_ERROR',
        10 => 'MEMCACHED_SERVER_ERROR',
        11 => 'MEMCACHED_ERROR',
        13 => 'MEMCACHED_DATA_DOES_NOT_EXIST',
        14 => 'MEMCACHED_NOTSTORED',
        16 => 'MEMCACHED_NOTFOUND',
        17 => 'MEMCACHED_MEMORY_ALLOCATION_FAILURE',
        18 => 'MEMCACHED_PARTIAL_READ',
        19 => 'MEMCACHED_SOME_ERRORS',
        20 => 'MEMCACHED_NO_SERVERS',
        21 => 'MEMCACHED_END',
        26 => 'MEMCACHED_ERRNO',
        27 => 'MEMCACHED_FAIL_UNIX_SOCKET',
        28 => 'MEMCACHED_NOT_SUPPORTED',
        29 => 'MEMCACHED_NO_KEY_PROVIDED',
        30 => 'MEMCACHED_FETCH_NOTFINISHED',
        31 => 'MEMCACHED_TIMEOUT',
        33 => 'MEMCACHED_BAD_KEY_PROVIDED',
        34 => 'MEMCACHED_INVALID_HOST_PROTOCOL',
        35 => 'MEMCACHED_SERVER_MARKED_DEAD',
        36 => 'MEMCACHED_UNKNOWN_STAT_KEY',
        37 => 'MEMCACHED_E2BIG',
        38 => 'MEMCACHED_INVALID_ARGUMENTS',
        39 => 'MEMCACHED_KEY_TOO_BIG',
        40 => 'MEMCACHED_AUTH_PROBLEM',
        41 => 'MEMCACHED_AUTH_FAILURE',
        42 => 'MEMCACHED_AUTH_CONTINUE',
        43 => 'MEMCACHED_PARSE_ERROR',
        44 => 'MEMCACHED_PARSE_USER_ERROR',
        45 => 'MEMCACHED_DEPRECATED',
        46 => 'MEMCACHED_IN_PROGRESS',
        47 => 'MEMCACHED_SERVER_TEMPORARILY_DISABLED',
        48 => 'MEMCACHED_SERVER_MEMORY_ALLOCATION_FAILURE',
        49 => 'MEMCACHED_MAXIMUM_RETURN',
    ];

    /**
     * Available memcached option type strings to their object constant.
     *
     * @var string[]
     */
    protected static $optionTypes = [
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
     * @var string[]
     */
    protected static $optionValues = [
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
     * Options array to load into Memcached when initialized.
     *
     * @var string[]
     */
    protected $options = [];

    /**
     * Servers array to load into Memcached when initialized.
     *
     * @var array[]
     */
    protected $servers = [];

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

        $this->setInitialized(false);
    }

    /**
     * Utilize lazy initialization to avoid needless creation of the memcached object
     *
     * @return bool
     */
    protected function lazyInitialize()
    {
        if ($this->isInitialized()) {
            return true;
        }

        if ($this->isSupported() === false || $this->isEnabled() === false) {
            return false;
        }

        $this->makeMemcachedInstance();
        $this->applyMemcachedOptions();
        $this->applyMemcachedServers();

        $this->setInitialized(true);

        return true;
    }

    /**
     * Instantiate memcached class if not already created.
     *
     * @return $this
     */
    protected function makeMemcachedInstance()
    {
        if (false === ($this->memcached instanceof Memcached)) {
            $this->memcached = new Memcached();
        }

        return $this;
    }

    /**
     * An array of option definitions as passed by the DI compiler pass.
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options = [])
    {
        $this->options = $options;
        $this->setInitialized(false);

        return $this;
    }

    /**
     * Gets the value of a memcached configured option.
     *
     * @param $memcachedOptionConstant
     *
     * @return mixed|false
     */
    public function getOption($memcachedOptionConstant)
    {
        if ($this->lazyInitialize() === false) {
            return false;
        }

        return $this->memcached->getOption($memcachedOptionConstant);
    }

    /**
     * An array of option definitions as passed by the DI compiler pass.
     *
     * @return $this
     */
    protected function applyMemcachedOptions()
    {
        $resolvedOptions = [];

        foreach ($this->options as $type => $value) {
            $this->handleOptionResolution($resolvedOptions, $type, $value);
        }

        $this->memcached->setOptions($resolvedOptions);

        return $this;
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
        if (false === array_key_exists($type, self::$optionTypes)) {
            throw new RuntimeException(
                'Unknown memcached option type %s specified in "%s".',
                null, null, null, $type, __METHOD__
            );
        }

        return self::$optionTypes[ $type ];
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
        if (true === array_key_exists($type, self::$optionValues) &&
            true === array_key_exists($value, self::$optionValues[ $type ])) {
            return self::$optionValues[ $type ][ $value ];
        }

        return $value;
    }

    /**
     * Array of server definitions to set (generally passed by Symfony's DI).
     *
     * @param array $servers
     *
     * @return $this
     */
    public function setServers(array $servers = [])
    {
        $this->servers = $servers;
        $this->setInitialized(false);

        return $this;
    }

    /**
     * Array of server definitions to add. Unlike {@see:setServers} this method
     * does not reset previously configured servers. Be careful about adding
     * duplicate entries, as no check is made to disallow such.
     *
     * @param array $servers
     *
     * @return $this
     */
    public function addServers(array $servers = [])
    {
        foreach ($servers as $s) {
            $this->servers[] = $s;
        }

        $this->setInitialized(false);

        return $this;
    }

    /**
     * Array of server definitions to set (generally passed by Symfony's DI).
     *
     * @return $this
     */
    protected function applyMemcachedServers()
    {
        $resolvedServers = [];
        foreach ($this->servers as $name => $parameters) {
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
                'Unknown number of server connection parameters. Please provide 3: ip/host, port, and weight in "%s".',
                null, null, null, __METHOD__
            );
        }

        $resolvedServers[$name] = array_values($parameters);
    }

    /**
     * Check if the handler type is supported using the default decider implementation.
     *
     * @param mixed,... $by
     *
     * @return bool
     */
    protected function isSupportedDefaultDecider(...$by)
    {
        if (false === $this->isEnabled()) {
            return false;
        }

        $hasValidOrmExtension = Extension::isEnabled('memcached');

        return (bool) ($hasValidOrmExtension !== false ?: false);
    }

    /**
     * Get the result of the last operation based on a comparison of the expected
     * return code and the received return code via an optionally custom callable.
     *
     * @param int                    $expectedCode
     * @param callable|\Closure|null $decider
     *
     * @return bool
     */
    protected function isLastActionSuccessful($expectedCode = Memcached::RES_SUCCESS, callable $decider = null)
    {
        if (null === $decider) {
            $decider = [$this, 'getReturnValueStatus'];
        }

        return (bool) $decider($expectedCode, $this->memcached->getResultCode());
    }

    /**
     * Default action success decider.
     *
     * @param int $expectedCode
     * @param int $receivedCode
     *
     * @return bool
     */
    protected function getReturnValueStatus($expectedCode, $receivedCode)
    {
        return (bool) (
            (int) $expectedCode === (int) $receivedCode ||
            in_array((int) $receivedCode, self::$memcachedRetSuccess, true)
        );
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
