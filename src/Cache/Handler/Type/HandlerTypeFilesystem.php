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
     * Directory to write cache data files to (generally your system temp dir)
     *
     * @var string|null
     */
    protected $cacheDirectory = null;

    /**
     * Check if the handler type is supported by the current environment
     *
     * @return bool
     */
    public function isSupported()
    {
        return (bool) (true === $this->hasCacheDirectory());
    }

    /**
     * Directory set via DI setter injection, verifies the directory exists, is
     * writable, and as a last resort attempts to create it.
     *
     * @param string $directory
     */
    public function proposeCacheDirectory($directory)
    {
        $requiredSubDirectory = 'scribe_cache';

        if ($requiredSubDirectory !== substr($directory, -12) &&
            $requiredSubDirectory !== substr($directory, -13))
        {
            $directory .= DIRECTORY_SEPARATOR . $requiredSubDirectory;
        }

        if ((true === is_dir($directory) && true === is_writable($directory)) ||
            true === mkdir($directory, 0777, true))
        {
            $this->setCacheDirectory($directory);
        }
    }

    /**
     * Set the cache directory path
     *
     * @param string|null $dir
     */
    protected function setCacheDirectory($dir)
    {
        $this->cacheDirectory = $dir;
    }

    /**
     * Get the cache directory path
     *
     * @return string|null
     */
    protected function getCacheDirectory()
    {
        return $this->cacheDirectory;
    }

    /**
     * Determine if a cache directory was validated and set
     *
     * @return bool
     */
    protected function hasCacheDirectory()
    {
        return (bool) (null !== $this->cacheDirectory);
    }

    /**
     * Get the fully-qualified file path for a given cache key
     *
     * @param  string $key
     * @return string
     */
    protected function getCacheFilePath($key)
    {
        return (string) $this->getCacheDirectory() . DIRECTORY_SEPARATOR . $key . '.cache';
    }


    /**
     * Retrieve the cached data using the provided key
     *
     * @param  string $key
     * @return string|null
     */
    protected function getUsingHandler($key)
    {
        if (true === $this->hasUsingHandler($key) &&
            false !== ($value = file_get_contents($this->getCacheFilePath($key))))
        {
            return $value;
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
        return (false !== file_put_contents($this->getCacheFilePath($key), $data, LOCK_EX));
    }

    /**
     * Check if the cached data exists using the provided key (and clean stale
     * file if exists).
     *
     * @param  string $key
     * @return bool
     */
    protected function hasUsingHandler($key)
    {
        $filePath = $this->getCacheFilePath($key);

        if (true === file_exists($filePath)) {
            if (true === ((time() - filemtime($filePath)) < $this->getTtl())) {

                return true;
            }

            $this->delUsingHandler($key);
        }

        return false;
    }

    /**
     * Delete the cached data using the provided key
     *
     * @param  string $key
     * @return bool
     */
    protected function delUsingHandler($key)
    {
        return (bool) (true === unlink($this->getCacheFilePath($key)));
    }

    /**
     * Flush all cached data within this cache mechanism-type
     *
     * @return bool
     */
    protected function flushAllUsingHandler()
    {
        $cacheFiles = glob($this->getCacheDirectory() . '/'.$this->getKeyGenerator()->getKeyPrefix().'*');

        foreach ($cacheFiles as $file) {
            unlink($file);
        }

        return true;
    }
}

/* EOF */
