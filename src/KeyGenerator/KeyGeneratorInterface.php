<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\KeyGenerator;

/**
 * Interface KeyGeneratorInterface.
 */
interface KeyGeneratorInterface
{
    /**
     * The default hash algorithm to use for generating the cache key.
     *
     * @var int
     */
    const MODE_KEY_HASH_METHOD_DEFAULT = self::MODE_KEY_HASH_METHOD_MD5;

    /**
     * Use simple md5 function call to generate cache key.
     *
     * @var int
     */
    const MODE_KEY_HASH_METHOD_MD5 = 100;

    /**
     * Use simple sha1 function call to generate cache key.
     *
     * @var int
     */
    const MODE_KEY_HASH_METHOD_SHA1 = 101;

    /**
     * Use callable (closure) function call to generate cache key.
     *
     * @var int
     */
    const MODE_KEY_HASH_METHOD_CLOSURE = 199;

    /**
     * The default method for translating the passed key values to strings.
     *
     * @var int
     */
    const MODE_VALUES_TRANSLATION_METHOD_DEFAULT = self::MODE_VALUES_TRANSLATION_METHOD_INTERNAL;

    /**
     * Use the internal method for translating the passed key values to strings.
     *
     * @var int
     */
    const MODE_VALUES_TRANSLATION_METHOD_INTERNAL = 200;

    /**
     * Use callable (closure) method for translating the passed key values to strings.
     *
     * @var int
     */
    const MODE_VALUES_TRANSLATION_METHOD_CLOSURE = 299;

    /**
     * Set the key prefix string.
     *
     * @var string
     *
     * @return $this
     */
    public function setKeyPrefix($prefix = '');

    /**
     * Set the key prefix string.
     *
     * @return string
     */
    public function getKeyPrefix();

    /**
     * Set the final translated and hashed key string.
     *
     * @var string
     *
     * @return $this
     */
    public function setKeyString($key);

    /**
     * Get the final translated and hashed key string.
     *
     * @return string|null
     */
    public function getKeyString();

    /**
     * Check if the final translated and hashed key string exists.
     *
     * @return bool
     */
    public function hasKeyString();

    /**
     * Set the values used to generate the key.
     *
     * @param ...mixed $values
     *
     * @return $this
     */
    public function setKeyValues(...$values);

    /**
     * Add to the values used to generate the key.
     *
     * @param ...mixed $values
     *
     * @return $this
     */
    public function addKeyValues(...$values);

    /**
     * Get the values used to generate the key.
     *
     * @return mixed[]
     */
    public function getKeyValues();

    /**
     * Check if any values used to generate the key exist.
     *
     * @return bool
     */
    public function hasKeyValues();

    /**
     * Set the translated values used to generate the key.
     *
     * @param ...string $values
     *
     * @return $this
     */
    public function setKeyValuesTranslated(...$values);

    /**
     * Add to the translated values used to generate the key.
     *
     * @param ...string $values
     *
     * @return $this
     */
    public function addKeyValuesTranslated(...$values);

    /**
     * Get the translated values used to generate the key.
     *
     * @return string[]
     */
    public function getKeyValuesTranslated();

    /**
     * Check if any translated values used to generate the key exist.
     *
     * @return bool
     */
    public function hasKeyValuesTranslated();

    /**
     * Set the mode used to traverse over and translate the passed key values to strings.
     *
     * @param int $mode
     *
     * @return $this
     */
    public function setKeyValuesTranslationMode($mode = self::MODE_VALUES_TRANSLATION_METHOD_DEFAULT);

    /**
     * Get the mode used to traverse over and translate the passed key values to strings.
     *
     * @return int
     */
    public function getKeyValuesTranslationMode();

    /**
     * Set the callable (closure) used to traverse over and translate the passed key values to strings.
     *
     * @param callable|null $closure
     *
     * @return $this
     */
    public function setKeyValuesTranslationClosure(callable $closure = null);

    /**
     * Get the callable (closure) used to traverse over and translate the passed key values to strings.
     *
     * @return callable|null
     */
    public function getKeyValuesTranslationClosure();

    /**
     * Check if a callable (closure) has been set for traversing over and translating the passed key values to strings.
     *
     * @return bool
     */
    public function hasKeyValuesTranslationClosure();

    /**
     * Set the mode used to generate the final cache key.
     *
     * @param int $mode
     *
     * @return $this
     */
    public function setKeyHashMode($mode = self::MODE_KEY_HASH_METHOD_DEFAULT);

    /**
     * Get the mode used to generate the final cache key.
     *
     * @return int
     */
    public function getKeyHashMode();

    /**
     * Set callable (closure) used to hash values to generate the final cache key.
     *
     * @param callable|null $closure
     *
     * @return $this
     */
    public function setKeyHashClosure(callable $closure = null);

    /**
     * Set callable (closure) used to hash values to generate the final cache key.
     *
     * @return callable|null
     */
    public function getKeyHashClosure();

    /**
     * Check if callable (closure) used to hash values to generate the final cache key exists.
     *
     * @return bool
     */
    public function hasKeyHashClosure();

    /**
     * Get the key final key using the requested method for values translation and
     * hash generation.
     *
     * @param ...mixed $values
     *
     * @return string
     */
    public function getKey(...$values);
}

/* EOF */
