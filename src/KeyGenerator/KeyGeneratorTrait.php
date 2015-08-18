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

use Scribe\Utility\Serializer\Serializer;
use Scribe\CacheBundle\Exceptions\InvalidArgumentException;
use Scribe\CacheBundle\Exceptions\RuntimeException;

/**
 * Trait KeyGeneratorTrait.
 */
trait KeyGeneratorTrait
{
    /**
     * The key prefix string.
     *
     * @var string
     */
    protected $keyPrefix = 'scribe_cache';

    /**
     * The final translated and hashed key string.
     *
     * @var string|null
     */
    protected $keyString = null;

    /**
     * An array of values to create the cache key from.
     *
     * @var mixed[]
     */
    protected $keyValues = [];

    /**
     * An array of values to create the cache key from.
     *
     * @var string[]
     */
    protected $keyValuesTranslated = [];

    /**
     * The key values translation method to use prior.
     *
     * @var int
     */
    protected $keyValuesTranslationMode = KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_DEFAULT;

    /**
     * The optional callable (closure) for key value translation.
     *
     * @var callable|null
     */
    protected $keyValuesTranslationClosure = null;

    /**
     * The key hash mode used to generate the final cache key.
     *
     * @var int
     */
    protected $keyHashMode = KeyGeneratorInterface::MODE_KEY_HASH_METHOD_DEFAULT;

    /**
     * The optional callable (closure) for key hash generation.
     *
     * @var callable|null
     */
    protected $keyHashClosure = null;

    /**
     * Set the key prefix string.
     *
     * @var string
     *
     * @return $this
     */
    public function setKeyPrefix($prefix = '')
    {
        $this->keyPrefix = (string) $prefix;

        return $this;
    }

    /**
     * Set the key prefix string.
     *
     * @return string
     */
    public function getKeyPrefix()
    {
        return $this->keyPrefix;
    }

    /**
     * Set the final translated and hashed cache key based on the values provided.
     *
     * @param string $string
     *
     * @return $this
     *
     * @throws RuntimeException
     */
    public function setKeyString($string)
    {
        if (true !== is_string($string)) {
            throw new RuntimeException(
                'The final translated and hashed key must be a string in "%s".',
                null, null, null, __METHOD__
            );
        }

        $this->keyString = $string;

        return $this;
    }

    /**
     * Get the final key string.
     *
     * @return string|null
     */
    public function getKeyString()
    {
        return $this->keyString;
    }

    /**
     * Check for final key string.
     *
     * @return bool
     */
    public function hasKeyString()
    {
        return (bool) (
            null !== $this->keyString &&
            true === (strlen($this->keyString) > 0)
        );
    }

    /**
     * Set the values used to generate the key.
     *
     * @param ...mixed $values
     *
     * @return $this
     */
    public function setKeyValues(...$values)
    {
        if (false === is_array($values) || false === (count($values) > 0)) {
            $this->keyValues = [];
        } else {
            $this->keyValues = $values;
        }

        return $this;
    }

    /**
     * Add to the values used to generate the key.
     *
     * @param ...mixed $values
     *
     * @return $this
     */
    public function addKeyValues(...$values)
    {
        if (true === is_array($values) || true === (count($values) > 0)) {
            $this->keyValues = array_merge($this->keyValues, $values);
        }

        return $this;
    }

    /**
     * Get the values used to generate the key.
     *
     * @return mixed[]
     */
    public function getKeyValues()
    {
        return (array) $this->keyValues;
    }

    /**
     * Check if any values used to generate the key exist.
     *
     * @return bool
     */
    public function hasKeyValues()
    {
        return (bool) (
            true === is_array($this->keyValues) &&
            true === (count($this->keyValues) > 0)
        );
    }

    /**
     * Set the translated values used to generate the key.
     *
     * @param ...string $values
     *
     * @return $this
     */
    public function setKeyValuesTranslated(...$values)
    {
        if (false === is_array($values) || false === (count($values) > 0)) {
            $this->keyValuesTranslated = [];
        } else {
            $this->validateKeyValuesTranslated(...$values);
            $this->keyValuesTranslated = $values;
        }

        return $this;
    }

    /**
     * Add to the translated values used to generate the key.
     *
     * @param string,... $values
     *
     * @return $this
     */
    public function addKeyValuesTranslated(...$values)
    {
        if (true === is_array($values) || true === (count($values) > 0)) {
            $this->validateKeyValuesTranslated(...$values);
            $this->keyValuesTranslated = array_merge($this->keyValuesTranslated, $values);
        }

        return $this;
    }

    /**
     * Validate that all supposedly translated key values are in fact strings.
     *
     * @param string,... $values
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    protected function validateKeyValuesTranslated(...$values)
    {
        foreach ($values as $v) {
            if (false === is_string($v)) {
                throw new InvalidArgumentException(
                    'A passed translated value was not properly converted to a string in "%s".',
                    null, null, null, __METHOD__
                );
            }
        }

        return $this;
    }

    /**
     * Get the translated values used to generate the key.
     *
     * @return string[]
     */
    public function getKeyValuesTranslated()
    {
        return (array) $this->keyValuesTranslated;
    }

    /**
     * Check if any translated values used to generate the key exist.
     *
     * @return bool
     */
    public function hasKeyValuesTranslated()
    {
        return (bool) (
            true === is_array($this->keyValuesTranslated) &&
            true === (count($this->keyValuesTranslated) > 0)
        );
    }

    /**
     * Set the mode used to traverse over and translate the passed key values to strings.
     *
     * @param int $mode
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function setKeyValuesTranslationMode($mode = KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_DEFAULT)
    {
        if (false === is_int($mode)) {
            throw new InvalidArgumentException(
                'An invalid key for values translation mode was detected in "%s".',
                null, null, null, __METHOD__
            );
        }

        if ($mode !== KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_INTERNAL &&
            $mode !== KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_CLOSURE
        ) {
            throw new InvalidArgumentException(
                'An invalid key for values translation mode of %d was detected and cannot be used in "%s".',
                null, null, null, $mode, __METHOD__
            );
        }

        $this->keyValuesTranslationMode = $mode;

        return $this;
    }

    /**
     * Get the mode used to traverse over and translate the passed key values to strings.
     *
     * @return int
     */
    public function getKeyValuesTranslationMode()
    {
        return $this->keyValuesTranslationMode;
    }

    /**
     * Set the callable (closure) used to traverse over and translate the passed key values to strings.
     *
     * @param callable|null $closure
     *
     * @return $this
     */
    public function setKeyValuesTranslationClosure(callable $closure = null)
    {
        $this->keyValuesTranslationClosure = $closure;

        return $this;
    }

    /**
     * Get the callable (closure) used to traverse over and translate the passed key values to strings.
     *
     * @return callable|null
     */
    public function getKeyValuesTranslationClosure()
    {
        return $this->keyValuesTranslationClosure;
    }

    /**
     * Check if a callable (closure) used to traverse over and translate the passed key values to string has been defined.
     *
     * @return bool
     */
    public function hasKeyValuesTranslationClosure()
    {
        return (bool) (true === ($this->keyValuesTranslationClosure instanceof \Closure));
    }

    /**
     * Set the mode used to generate the final cache key.
     *
     * @param int $mode
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function setKeyHashMode($mode = KeyGeneratorInterface::MODE_KEY_HASH_METHOD_DEFAULT)
    {
        if (false === is_int($mode)) {
            throw new InvalidArgumentException(
                'An invalid key for hash mode was detected in "%s".',
                null, null, null, __METHOD__
            );
        }

        if ($mode !== KeyGeneratorInterface::MODE_KEY_HASH_METHOD_MD5 &&
            $mode !== KeyGeneratorInterface::MODE_KEY_HASH_METHOD_SHA1 &&
            $mode !== KeyGeneratorInterface::MODE_KEY_HASH_METHOD_CLOSURE
        ) {
            throw new InvalidArgumentException(
                'An invalid key for hash mode of %d was detected and cannot be used in "%s".',
                null, null, null, $mode, __METHOD__
            );
        }

        $this->keyHashMode = $mode;

        return $this;
    }

    /**
     * Set the mode used to generate the final cache key.
     *
     * @return int
     */
    public function getKeyHashMode()
    {
        return (int) $this->keyHashMode;
    }

    /**
     * Set callable (closure) used to hash values to generate the final cache key.
     *
     * @param callable|null $closure
     *
     * @return $this
     */
    public function setKeyHashClosure(callable $closure = null)
    {
        $this->keyHashClosure = $closure;

        return $this;
    }

    /**
     * Set callable (closure) used to hash values to generate the final cache key.
     *
     * @return callable|null
     */
    public function getKeyHashClosure()
    {
        return $this->keyHashClosure;
    }

    /**
     * Check if callable (closure) used to hash values to generate the final cache key exists.
     *
     * @return bool
     */
    public function hasKeyHashClosure()
    {
        return (bool) (true === ($this->keyHashClosure instanceof \Closure));
    }

    /**
     * Get cache key, overwriting previously set values to generate key if provided.
     *
     * @param ...mixed $values
     *
     * @return string
     */
    public function getKey(...$values)
    {
        if (true === is_array($values) && (true === (count($values) > 0))) {
            $this->setKeyValues(...$values);
        }

        $this
            ->handleKeyValuesTranslation()
            ->handleKeyValuesTranslatedHashing()
        ;

        return (string) $this->getKeyPrefix().'---'.$this->getKeyString();
    }

    /**
     * Handle translation of the provided key values to a string format.
     *
     * @return $this
     *
     * @throws RuntimeException
     */
    protected function handleKeyValuesTranslation()
    {
        if (false === $this->hasKeyValues()) {
            throw new RuntimeException(
                'Could not generate key without any values provided to base the key on in "%s".',
                null, null, null, __METHOD__
            );
        }

        $values = $this->getKeyValues();
        $mode = $this->getKeyValuesTranslationMode();

        if ($mode === KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_INTERNAL) {
            $valuesTranslated = $this->handleKeyValuesTranslationInternal(...$values);
        } elseif ($mode === KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_CLOSURE) {
            $valuesTranslated = $this->handleKeyValuesTranslationClosure(...$values);
        } else {
            throw new RuntimeException(
                'Could not handle key values translation during key generation as invalid mode was set in "%s".',
                null, null, null, __METHOD__
            );
        }

        $this->setKeyValuesTranslated(...$valuesTranslated);

        return $this;
    }

    /**
     * Handle translation of the provided key values to a string format via PHP's serialize function (default).
     *
     * @param ...mixed $values
     *
     * @return string[]
     *
     * @throws RuntimeException
     */
    protected function handleKeyValuesTranslationInternal(...$values)
    {
        $valuesTranslated = [];

        foreach ($values as $v) {
            if (true === is_resource($v)) {
                throw new RuntimeException(
                    'PHP resources (such as DB connections, file handles, etc) cannot be used as key values using the internal translation method in "%s".',
                    null, null, null, __METHOD__
                );
            }

            $valuesTranslated[ ] = Serializer::sleep($v);
        }

        return (array) $valuesTranslated;
    }

    /**
     * Handle translation of the provided key values to a string format via a user-defined closure.
     *
     * @param ...mixed $values
     *
     * @return string[]
     *
     * @throws RuntimeException
     */
    protected function handleKeyValuesTranslationClosure(...$values)
    {
        if (false === $this->hasKeyValuesTranslationClosure()) {
            throw new RuntimeException(
                'Could not handle key value translation as closure mode was set but no closure was defined in "%s".',
                null, null, null, __METHOD__
            );
        }

        $closure = $this->getKeyValuesTranslationClosure();
        $valuesTranslated = $closure(...$values);

        return (array) $valuesTranslated;
    }

    /**
     * Handle generating final key hash based on translated key values.
     *
     * @return $this
     *
     * @throws RuntimeException
     */
    protected function handleKeyValuesTranslatedHashing()
    {
        if (false === $this->hasKeyValuesTranslated()) {
            throw new RuntimeException(
                'Could not generate key without any translated values provided to base the key on in "%s".',
                null, null, null, __METHOD__
            );
        }

        $values = $this->getKeyValuesTranslated();
        $mode = $this->getKeyHashMode();

        if ($mode === KeyGeneratorInterface::MODE_KEY_HASH_METHOD_MD5) {
            $key = $this->handleKeyValuesTranslatedHashingInternal('md5', ...$values);
        } elseif ($mode === KeyGeneratorInterface::MODE_KEY_HASH_METHOD_SHA1) {
            $key = $this->handleKeyValuesTranslatedHashingInternal('sha1', ...$values);
        } elseif ($mode === KeyGeneratorInterface::MODE_KEY_HASH_METHOD_CLOSURE) {
            $key = $this->handleKeyValuesTranslatedHashingClosure(...$values);
        } else {
            throw new RuntimeException(
                'Could not handle key hashing during key generation as invalid mode was set in "%s".',
                null, null, null, __METHOD__
            );
        }

        $this->setKeyString($key);

        return $this;
    }

    /**
     * Handle generating final key hash based on translated key values using a natively supported hash algorithm.
     *
     * @param string    $hashAlgorithm
     * @param ...string $values
     *
     * @return string
     */
    protected function handleKeyValuesTranslatedHashingInternal($hashAlgorithm, ...$values)
    {
        $key = hash(
            $hashAlgorithm,
            Serializer::sleep($values),
            false
        );

        return (string) $key;
    }

    /**
     * Handle generating final key hash based on translated key values using a passed closure function.
     *
     * @param ...string $values
     *
     * @return string
     *
     * @throws RuntimeException
     */
    protected function handleKeyValuesTranslatedHashingClosure(...$values)
    {
        if (false === $this->hasKeyHashClosure()) {
            throw new RuntimeException(
                'Could not handle key hashing as closure mode was set but no closure was defined in "%s".',
                null, null, null, __METHOD__
            );
        }

        $closure = $this->getKeyHashClosure();
        $key = $closure(...$values);

        return (string) $key;
    }
}

/* EOF */
