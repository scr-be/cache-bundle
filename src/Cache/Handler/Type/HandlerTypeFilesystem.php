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

/**
 * Class HandlerTypeFilesystem
 *
 * @package Scribe\CacheBundle\Cache\Handler\Type
 */
class HandlerTypeFilesystem extends AbstractHandlerType
{
    /**
     * System temporary directory
     *
     * @var string|null
     */
    protected $tempDir;

    /**
     * Setup the class instance with the required properties
     *
     * @param KeyGeneratorInterface $keyGenerator
     */
    public function __construct(KeyGeneratorInterface $keyGenerator = null, $ttl = 600)
    {
        parent::__construct($keyGenerator, $ttl);

        $this->setAndValidateTempDir();
    }

    /**
     * Check if the handler type is supported by the current environment
     *
     * @return bool
     */
    public function isSupported()
    {
        return (bool) (null !== $this->getTempDir());
    }

    /**
     * Set, make, and validate temporary directory
     *
     * @return $this
     */
    protected function setAndValidateTempDir()
    {
        $tempDirBase = sys_get_temp_dir();
        $tempDir     = $tempDirBase . DIRECTORY_SEPARATOR . 'scribe_cache';

        if (true === is_dir($tempDirBase) && true === is_writable($tempDirBase)) {
            if ((true === is_dir($tempDir) && true === is_writable($tempDir)) ||
                (false === is_dir($tempDir) &&  true === mkdir($tempDir)))
            {
                $this->setTempDir($tempDir);
            }
        }

        return $this;
    }

    /**
     * Set the temp dir
     *
     * @param $dir
     */
    protected function setTempDir($dir)
    {
        $this->tempDir = $dir;
    }

    /**
     * Get temp dir
     *
     * @return string|null
     */
    protected function getTempDir()
    {
        return $this->tempDir;
    }

    /**
     * Check if temp dir exists
     *
     * @return bool
     */
    protected function hasTempDir()
    {
        return (bool) (null !== $this->tempDir);
    }

    /**
     * Get the cache file path for a given key
     *
     * @param  string $key
     * @return string
     */
    protected function getCacheFilePath($key)
    {
        return (string) $this->getTempDir() . DIRECTORY_SEPARATOR . $key . '.cache';
    }

    /**
     * Get the cached value.
     *
     * @param  string $key
     * @return string
     */
    protected function getValueViaHandlerImplementation($key)
    {
        $filePath = $this->getCacheFilePath($key);

        if (true === $this->hasValueViaHandlerImplementation($key) &&
            false !== ($value = file_get_contents($filePath)))
        {
            return $value;
        }

        return null;
    }

    /**
     * Set the cached value.
     *
     * @param  string $data
     * @param  string $key
     * @return $this
     */
    protected function setValueViaHandlerImplementation($data, $key)
    {
        file_put_contents($this->getCacheFilePath($key), $data);

        return $this;
    }

    /**
     * Check for the cached value.
     *
     * @param  string $key
     * @return bool
     */
    protected function hasValueViaHandlerImplementation($key)
    {
        $filePath = $this->getCacheFilePath($key);

        if (true === file_exists($filePath) &&
            true === ((time() - filemtime($filePath)) <= $this->getTtl()))
        {
            return true;
        }

        return false;
    }
}

/* EOF */
