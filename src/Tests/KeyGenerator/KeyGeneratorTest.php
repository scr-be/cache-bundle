<?php

/*
 * This file is part of the Scribe Cache Bundle.
 *
 * (c) Scribe Inc. <source@scribe.software>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\CacheBundle\Tests\KeyGenerator;

use Scribe\Utility\UnitTest\AbstractMantleTestCase;
use Scribe\Utility\Serializer\Serializer;
use Scribe\CacheBundle\KeyGenerator\KeyGenerator;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorInterface;

/**
 * Class KeyGeneratorTest.
 */
class KeyGeneratorTest extends AbstractMantleTestCase
{
    const FULLY_QUALIFIED_CLASS_NAME = 'Scribe\CacheBundle\KeyGenerator\KeyGenerator';

    public $testResource;

    public function setUp()
    {
        parent::setUp();

        $this->testResource = fopen(__FILE__, 'r');
    }

    public function getNewKeyGenerator()
    {
        return new KeyGenerator();
    }

    public function getKeyValuesTranslationClosure()
    {
        return function (...$values) {
            $valuesTranslated = [];
            foreach ($values as $v) {
                $valuesTranslated[ ] = Serializer::sleep($v);
            }

            return $valuesTranslated;
        };
    }

    public function getKeyHashClosure()
    {
        return function (...$values) {
            $newValues = [];
            foreach ($values as $v) {
                $newValues[ ] = Serializer::sleep($v.'Closure');
            }

            return hash('sha512', implode('', $newValues), false);
        };
    }

    public function getReflectionForMethod($method)
    {
        $refFormat = new \ReflectionClass(self::FULLY_QUALIFIED_CLASS_NAME);
        $method = $refFormat->getMethod($method);
        $method->setAccessible(true);

        return [
            $this->getNewKeyGenerator(),
            $method,
        ];
    }

    public function getReflectionForProperty($property)
    {
        $refFormat = new \ReflectionClass(self::FULLY_QUALIFIED_CLASS_NAME);
        $property = $refFormat->getProperty($property);
        $property->setAccessible(true);

        return [
            $this->getNewKeyGenerator(),
            $property,
        ];
    }

    public function getReflectionForMethodAndProperty($method, $property)
    {
        $refFormat = new \ReflectionClass(self::FULLY_QUALIFIED_CLASS_NAME);
        $method = $refFormat->getMethod($method);
        $method->setAccessible(true);
        $property = $refFormat->getProperty($property);
        $property->setAccessible(true);

        return [
            $this->getNewKeyGenerator(),
            $method,
            $property,
        ];
    }

    /**
     * @group KeyGenerator
     */
    public function testCanInstantiateClass()
    {
        static::assertInstanceOf(
            self::FULLY_QUALIFIED_CLASS_NAME,
            $this->getNewKeyGenerator()
        );
    }

    /**
     * @group KeyGenerator
     */
    public function testKeyStringMutatorMethods()
    {
        $kg = $this->getNewKeyGenerator();
        static::assertNotTrue($kg->hasKeyString());
        static::assertNull($kg->getKeyString());

        $kg->setKeyString('some-key');
        static::assertEquals('some-key', $kg->getKeyString());
        static::assertTrue($kg->hasKeyString());
    }

    /**
     * @group KeyGenerator
     */
    public function testKeyStringSetterExceptionHandling()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            '#The final translated and hashed key must be a string in .*#'
        );

        $this
            ->getNewKeyGenerator()
            ->setKeyString(0123)
        ;
    }

    /**
     * @group KeyGenerator
     */
    public function testKeyValuesMutatorMethods()
    {
        $kg = $this->getNewKeyGenerator();
        static::assertNotTrue($kg->hasKeyValues());
        static::assertEquals([], $kg->getKeyValues());

        $kg->setKeyValues('val1', 'val2');
        static::assertEquals(['val1', 'val2'], $kg->getKeyValues());

        $kg->addKeyValues('val3');
        static::assertEquals(['val1', 'val2', 'val3'], $kg->getKeyValues());
        static::assertTrue($kg->hasKeyValues());

        $kg->setKeyValues();
        static::assertNotTrue($kg->hasKeyValues());
    }

    /**
     * @group KeyGenerator
     */
    public function testKeyValuesTranslatedMutatorMethods()
    {
        $kg = $this->getNewKeyGenerator();
        static::assertNotTrue($kg->hasKeyValuesTranslated());
        static::assertEquals([], $kg->getKeyValuesTranslated());

        $kg->setKeyValuesTranslated('val1', 'val2');
        static::assertEquals(['val1', 'val2'], $kg->getKeyValuesTranslated());

        $kg->addKeyValuesTranslated('val3');
        static::assertEquals(['val1', 'val2', 'val3'], $kg->getKeyValuesTranslated());
        static::assertTrue($kg->hasKeyValuesTranslated());

        $kg->setKeyValuesTranslated();
        static::assertNotTrue($kg->hasKeyValuesTranslated());
    }

    /**
     * @group KeyGenerator
     */
    public function testKeyValuesTranslatedSetterExceptionHandling()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\InvalidArgumentException',
            '#A passed translated value was not properly converted to a string .*#'
        );

        $kg = $this
            ->getNewKeyGenerator()
            ->setKeyValuesTranslated((new \stdClass()))
        ;
    }

    /**
     * @group KeyGenerator
     */
    public function testKeyValuesTranslatedAdderExceptionHandling()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\InvalidArgumentException',
            '#A passed translated value was not properly converted to a string in .*#'
        );

        $kg = $this
            ->getNewKeyGenerator()
            ->addKeyValuesTranslated((new \stdClass()))
        ;
    }

    /**
     * @group KeyGenerator
     */
    public function testKeyValuesTranslationModeMutatorMethods()
    {
        $kg = $this->getNewKeyGenerator();
        static::assertEquals(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_DEFAULT, $kg->getKeyValuesTranslationMode());

        $kg->setKeyValuesTranslationMode(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_INTERNAL);
        static::assertEquals(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_INTERNAL, $kg->getKeyValuesTranslationMode());

        $kg->setKeyValuesTranslationMode(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_CLOSURE);
        static::assertEquals(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_CLOSURE, $kg->getKeyValuesTranslationMode());
    }

    /**
     * @group KeyGenerator
     */
    public function testKeyValuesTranslationModeSetterExceptionHandlingNonInt()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\InvalidArgumentException',
            '#An invalid key for values translation mode was detected in .*#'
        );

        $kg = $this
            ->getNewKeyGenerator()
            ->setKeyValuesTranslationMode('string')
        ;
    }

    /**
     * @group KeyGenerator
     */
    public function testKeyValuesTranslationModeSetterExceptionHandlingInvalidInt()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\InvalidArgumentException',
            '#An invalid key for values translation mode of 123456789 was detected and cannot be used in .*#'
        );

        $kg = $this
            ->getNewKeyGenerator()
            ->setKeyValuesTranslationMode(123456789)
        ;
    }

    /**
     * @group KeyGenerator
     */
    public function testKeyValuesTranslationClosureMutatorMethods()
    {
        $kg = $this->getNewKeyGenerator();
        static::assertNull($kg->getKeyValuesTranslationClosure());
        static::assertNotTrue($kg->hasKeyValuesTranslationClosure());

        $expected = $this->getKeyValuesTranslationClosure();
        $kg->setKeyValuesTranslationClosure($expected);
        static::assertTrue($kg->hasKeyValuesTranslationClosure());
        static::assertEquals($expected, $kg->getKeyValuesTranslationClosure());
        static::assertInstanceOf('Closure', $kg->getKeyValuesTranslationClosure());

        $kg->setKeyValuesTranslationClosure(null);
        static::assertNull($kg->getKeyValuesTranslationClosure());
        static::assertNotTrue($kg->hasKeyValuesTranslationClosure());
    }

    /**
     * @group KeyGenerator
     */
    public function testKeyValuesTranslationClosureSetterTypeHint()
    {
        $this->setExpectedExceptionRegExp(
            'PHPUnit_Framework_Error',
            '#.*Argument 1 passed to .*KeyGenerator::setKeyValuesTranslationClosure\(\) must be callable, string given, called in .*KeyGeneratorTest.php.*#'
        );

        $kg = $this
            ->getNewKeyGenerator()
            ->setKeyValuesTranslationClosure('not-a-closure')
        ;
    }

    /**
     * @group KeyGenerator
     */
    public function testKeyHashModeMutatorMethods()
    {
        $kg = $this->getNewKeyGenerator();
        static::assertEquals(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_DEFAULT, $kg->getKeyHashMode());

        $kg->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_MD5);
        static::assertEquals(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_MD5, $kg->getKeyHashMode());

        $kg->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_SHA1);
        static::assertEquals(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_SHA1, $kg->getKeyHashMode());

        $kg->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_SHA1);
        static::assertEquals(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_SHA1, $kg->getKeyHashMode());

        $kg->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_CLOSURE);
        static::assertEquals(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_CLOSURE, $kg->getKeyHashMode());
    }

    /**
     * @group KeyGenerator
     */
    public function testKeyHashModeSetterExceptionHandlingNonInt()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\InvalidArgumentException',
            '#An invalid key for hash mode was detected in .*#'
        );

        $kg = $this
            ->getNewKeyGenerator()
            ->setKeyHashMode('string')
        ;
    }

    /**
     * @group KeyGenerator
     */
    public function testKeyHashModeSetterExceptionHandlingInvalidInt()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\InvalidArgumentException',
            '#An invalid key for hash mode of 123456789 was detected and cannot be used in .*#'
        );

        $kg = $this
            ->getNewKeyGenerator()
            ->setKeyHashMode(123456789)
        ;
    }

    /**
     * @group KeyGenerator
     */
    public function testKeyHashClosureMutatorMethods()
    {
        $kg = $this->getNewKeyGenerator();
        static::assertNull($kg->getKeyHashClosure());
        static::assertNotTrue($kg->hasKeyHashClosure());

        $expected = $this->getKeyHashClosure();
        $kg->setKeyHashClosure($expected);
        static::assertTrue($kg->hasKeyHashClosure());
        static::assertEquals($expected, $kg->getKeyHashClosure());
        static::assertInstanceOf('Closure', $kg->getKeyHashClosure());

        $kg->setKeyHashClosure(null);
        static::assertNull($kg->getKeyHashClosure());
        static::assertNotTrue($kg->hasKeyHashClosure());
    }

    /**
     * @group KeyGenerator
     */
    public function testKeyHashClosureSetterTypeHint()
    {
        $this->setExpectedExceptionRegExp(
            'PHPUnit_Framework_Error',
            '#.*Argument 1 passed to .*KeyGenerator::setKeyHashClosure\(\) must be callable, string given, called in .*KeyGeneratorTest.php.*#'
        );

        $kg = $this
            ->getNewKeyGenerator()
            ->setKeyHashClosure('not-a-closure')
        ;
    }

    /**
     * @group KeyGenerator
     */
    public function testCanGenerateKeyShortForm()
    {
        $kg = $this->getNewKeyGenerator();
        $stdClass = new \stdClass();
        $stdClass->desc = 'This is a standard class!';

        $expectedKeyValues = [
            $stdClass,
            'string-value',
            123456789,
        ];
        $expectedKeyValuesTranslated = $expectedKeyValues;
        foreach ($expectedKeyValuesTranslated as &$gotKeyValue) {
            $gotKeyValue = Serializer::sleep($gotKeyValue);
        }
        $expectedKeyMd5 = 'scribe_cache---eafe0156bff82dfe6f89580709815c72';
        $expectedKeySha1 = 'scribe_cache---12dfdd120c0d9f80a717d721efdcaf38e833b002';
        $expectedKeyClosure = 'scribe_cache---acd5f359c29d59ca31a3210f5188eb47d4faa76e9d7d38dcae044226be30a6b88c1de35929531f1ecd0ba6dbef3adf0f47252d95777b3bceaae6f78ff7a46b15';

        $key = $kg->getKey(
            ...$expectedKeyValues
        );

        static::assertEquals($expectedKeyValues, $kg->getKeyValues());
        static::assertEquals($expectedKeyValuesTranslated, $kg->getKeyValuesTranslated());
        static::assertEquals($expectedKeyMd5, $kg->getKey());

        $kg->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_SHA1);
        static::assertEquals($expectedKeySha1, $kg->getKey());

        $kg->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_CLOSURE);
        $kg->setKeyHashClosure($this->getKeyHashClosure());
        static::assertEquals($expectedKeyClosure, $kg->getKey());
    }

    /**
     * @group KeyGenerator
     */
    public function testCanGenerateKeyLongForm()
    {
        $stdClass1 = new \stdClass();
        $stdClass1->desc = 'This is a standard class! The first!';
        $stdClass2 = new \stdClass();
        $stdClass2->desc = 'This is a standard class! The second!';

        $firstKeyValues = [
            'string-value',
            12345,
            $stdClass1,
        ];
        $secondKeyValues = [
            'string-value-2',
            67890,
            $stdClass2,
        ];
        $expectedKeyValues = array_merge($firstKeyValues, $secondKeyValues);
        $expectedKeyValuesTranslated = $expectedKeyValues;
        foreach ($expectedKeyValuesTranslated as &$gotKeyValue) {
            $gotKeyValue = Serializer::sleep($gotKeyValue);
        }

        $expectedKeyMd5 = 'scribe_cache---1bec05706e94f6002b5d942327e10fbe';
        $expectedKeySha1 = 'scribe_cache---cd1cfb98b005f2f45743ca6f308c4393779cd8e0';
        $expectedKeyClosure = 'scribe_cache---aa01f5eb5a96bf0096a5d59eba00b99becb173bbf208c6b7e40d39e0869908fb85c9382a4a46a5f83f16c03686781619347f5ce22cc61404702bcc4f73cac58b';

        $kg = $this->getNewKeyGenerator();
        $key = $kg
            ->setKeyValues(...$firstKeyValues)
            ->addKeyValues(...$secondKeyValues)
            ->setKeyValuesTranslationMode(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_DEFAULT)
            ->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_DEFAULT)
            ->getKey()
        ;

        static::assertEquals($expectedKeyValues, $kg->getKeyValues());

        static::assertEquals($expectedKeyValuesTranslated, $kg->getKeyValuesTranslated());
        static::assertEquals($expectedKeyMd5, $key);
        static::assertEquals($expectedKeyMd5, $kg->getKey());

        $kg = $this->getNewKeyGenerator();
        $key = $kg
            ->setKeyValues(...$firstKeyValues)
            ->addKeyValues(...$secondKeyValues)
            ->setKeyValuesTranslationMode(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_INTERNAL)
            ->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_DEFAULT)
            ->getKey()
        ;

        static::assertEquals($expectedKeyValues, $kg->getKeyValues());
        static::assertEquals($expectedKeyValuesTranslated, $kg->getKeyValuesTranslated());
        static::assertEquals($expectedKeyMd5, $key);
        static::assertEquals($expectedKeyMd5, $kg->getKey());

        $kg = $this->getNewKeyGenerator();
        $key = $kg
            ->setKeyValues(...$firstKeyValues)
            ->addKeyValues(...$secondKeyValues)
            ->setKeyValuesTranslationMode(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_CLOSURE)
            ->setKeyValuesTranslationClosure($this->getKeyValuesTranslationClosure())
            ->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_DEFAULT)
            ->getKey()
        ;

        static::assertEquals($expectedKeyValues, $kg->getKeyValues());
        static::assertEquals($expectedKeyValuesTranslated, $kg->getKeyValuesTranslated());
        static::assertEquals($expectedKeyMd5, $key);
        static::assertEquals($expectedKeyMd5, $kg->getKey());

        $kg = $this->getNewKeyGenerator();
        $key = $kg
            ->setKeyValues(...$firstKeyValues)
            ->addKeyValues(...$secondKeyValues)
            ->setKeyValuesTranslationMode(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_INTERNAL)
            ->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_MD5)
            ->getKey()
        ;

        static::assertEquals($expectedKeyValues, $kg->getKeyValues());
        static::assertEquals($expectedKeyValuesTranslated, $kg->getKeyValuesTranslated());
        static::assertEquals($expectedKeyMd5, $key);
        static::assertEquals($expectedKeyMd5, $kg->getKey());

        $kg = $this->getNewKeyGenerator();
        $key = $kg
            ->setKeyValues(...$firstKeyValues)
            ->addKeyValues(...$secondKeyValues)
            ->setKeyValuesTranslationMode(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_INTERNAL)
            ->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_SHA1)
            ->getKey()
        ;

        static::assertEquals($expectedKeyValues, $kg->getKeyValues());
        static::assertEquals($expectedKeyValuesTranslated, $kg->getKeyValuesTranslated());
        static::assertEquals($expectedKeySha1, $key);
        static::assertEquals($expectedKeySha1, $kg->getKey());

        $kg = $this->getNewKeyGenerator();
        $key = $kg
            ->setKeyValues(...$firstKeyValues)
            ->addKeyValues(...$secondKeyValues)
            ->setKeyValuesTranslationMode(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_INTERNAL)
            ->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_CLOSURE)
            ->setKeyHashClosure($this->getKeyHashClosure())
            ->getKey()
        ;

        static::assertEquals($expectedKeyValues, $kg->getKeyValues());
        static::assertEquals($expectedKeyValuesTranslated, $kg->getKeyValuesTranslated());
        static::assertEquals($expectedKeyClosure, $key);
        static::assertEquals($expectedKeyClosure, $kg->getKey());
    }

    /**
     * @group KeyGenerator
     */
    public function testHandleKeyValuesTranslationNoKeyValuesExceptionHandling()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            '#Could not generate key without any values provided to base the key .*#'
        );

        list($kg, $method) = $this->getReflectionForMethod('handleKeyValuesTranslation');

        $kg->setKeyValues();
        $method->invokeArgs($kg, []);
    }

    /**
     * @group KeyGenerator
     */
    public function testHandleKeyValuesTranslationInvalidModeExceptionHandling()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            '#Could not handle key values translation during key generation as invalid mode was set .*#'
        );

        list($kg, $method, $prop) = $this->getReflectionForMethodAndProperty('handleKeyValuesTranslation', 'keyValuesTranslationMode');

        $kg->setKeyValues('val1', 'val2');
        $prop->setValue($kg, 123456789);
        $method->invokeArgs($kg, []);
    }

    /**
     * @group KeyGenerator
     */
    public function testHandleKeyValuesTranslationInternalExceptionHandling()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            '#PHP resources \(such as DB connections, file handles, etc\) cannot be used as key values using the internal translation method .*#'
        );

        $kg = $this
            ->getNewKeyGenerator()
            ->getKey($this->testResource)
        ;
    }

    /**
     * @group KeyGenerator
     */
    public function testHandleKeyValuesTranslationClosureExceptionHandling()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            '#Could not handle key value translation as closure mode was set but no closure was defined .*#'
        );

        $key = $this
            ->getNewKeyGenerator()
            ->setKeyValues('val1', 'val2')
            ->setKeyValuesTranslationMode(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_CLOSURE)
            ->getKey()
        ;
    }

    /**
     * @group KeyGenerator
     */
    public function testHandleKeyValuesTranslatedHashingNoKeyExceptionHandling()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            '#Could not generate key without any translated values provided to base the key on .*#'
        );

        list($kg, $method) = $this->getReflectionForMethod('handleKeyValuesTranslatedHashing');

        $kg->setKeyValues();
        $method->invokeArgs($kg, []);
    }

    /**
     * @group KeyGenerator
     */
    public function testHandleKeyValuesTranslatedHashingInvalidModeExceptionHandling()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            '#Could not handle key hashing during key generation as invalid mode was set .*#'
        );

        list($kg, $method, $prop) = $this->getReflectionForMethodAndProperty('handleKeyValuesTranslatedHashing', 'keyHashMode');

        $kg->setKeyValuesTranslated('val1', 'val2');
        $prop->setValue($kg, 123456789);
        $method->invokeArgs($kg, []);
    }

    /**
     * @group KeyGenerator
     */
    public function testHandleKeyValuesTranslatedHashingClosureExceptionHandling()
    {
        $this->setExpectedExceptionRegExp(
            'Scribe\CacheBundle\Exceptions\RuntimeException',
            '#Could not handle key hashing as closure mode was set but no closure was defined .*#'
        );

        $key = $this
            ->getNewKeyGenerator()
            ->setKeyValues('val1', 'val2')
            ->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_CLOSURE)
            ->getKey()
        ;
    }

    public function tearDown()
    {
        fclose($this->testResource);

        parent::tearDown();
    }
}

/* EOF */
