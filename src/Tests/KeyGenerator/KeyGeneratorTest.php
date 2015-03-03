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

use PHPUnit_Framework_TestCase;
use Scribe\CacheBundle\KeyGenerator\KeyGenerator;
use Scribe\CacheBundle\KeyGenerator\KeyGeneratorInterface;

/**
 * Class KeyGeneratorTest
 *
 * @package Scribe\CacheBundle\Tests\KeyGenerator
 */
class KeyGeneratorTest extends PHPUnit_Framework_TestCase
{
    const FULLY_QUALIFIED_CLASS_NAME = 'Scribe\CacheBundle\KeyGenerator\KeyGenerator';

    protected $testResource;

    protected function setUp()
    {
        $this->testResource = fopen(__FILE__, 'r');
    }

    protected function getNewKeyGenerator()
    {
        return new KeyGenerator;
    }

    protected function getKeyValuesTranslationClosure()
    {
        return function(...$values) {
            $valuesTranslated = [ ];
            foreach ($values as $v) {
                $valuesTranslated[ ] = serialize($v);
            }

            return $valuesTranslated;
        };
    }

    protected function getKeyHashClosure()
    {
        return function(...$values) {
            $newValues = [ ];
            foreach ($values as $v) {
                $newValues[ ] = serialize($v . 'Closure');
            }

            return hash('sha512', implode('', $newValues), false);
        };
    }

    protected function getReflectionForMethod($method)
    {
        $refFormat = new \ReflectionClass(self::FULLY_QUALIFIED_CLASS_NAME);
        $method = $refFormat->getMethod($method);
        $method->setAccessible(true);

        return [
            $this->getNewKeyGenerator(),
            $method
        ];
    }

    protected function getReflectionForProperty($property)
    {
        $refFormat = new \ReflectionClass(self::FULLY_QUALIFIED_CLASS_NAME);
        $property = $refFormat->getProperty($property);
        $property->setAccessible(true);

        return [
            $this->getNewKeyGenerator(),
            $property
        ];
    }

    protected function getReflectionForMethodAndProperty($method, $property)
    {
        $refFormat = new \ReflectionClass(self::FULLY_QUALIFIED_CLASS_NAME);
        $method = $refFormat->getMethod($method);
        $method->setAccessible(true);
        $property = $refFormat->getProperty($property);
        $property->setAccessible(true);

        return [
            $this->getNewKeyGenerator(),
            $method,
            $property
        ];
    }

    public function testCanInstantiateClass()
    {
        $this->assertInstanceOf(
            self::FULLY_QUALIFIED_CLASS_NAME,
            $this->getNewKeyGenerator()
        );
    }

    public function testKeyStringMutatorMethods()
    {
        $kg = $this->getNewKeyGenerator();
        $this->assertNotTrue($kg->hasKeyString());
        $this->assertNull($kg->getKeyString());

        $kg->setKeyString('some-key');
        $this->assertEquals('some-key', $kg->getKeyString());
        $this->assertTrue($kg->hasKeyString());
    }

    /**
     * @expectedException        Scribe\CacheBundle\Exceptions\RuntimeException
     * @expectedExceptionMessage The final translated and hashed key must be a string.
     */
    public function testKeyStringSetterExceptionHandling()
    {
        $this
            ->getNewKeyGenerator()
            ->setKeyString(0123)
        ;
    }

    public function testKeyValuesMutatorMethods()
    {
        $kg = $this->getNewKeyGenerator();
        $this->assertNotTrue($kg->hasKeyValues());
        $this->assertEquals([ ], $kg->getKeyValues());

        $kg->setKeyValues('val1', 'val2');
        $this->assertEquals(['val1', 'val2'], $kg->getKeyValues());

        $kg->addKeyValues('val3');
        $this->assertEquals(['val1', 'val2', 'val3'], $kg->getKeyValues());
        $this->assertTrue($kg->hasKeyValues());

        $kg->setKeyValues();
        $this->assertNotTrue($kg->hasKeyValues());
    }

    public function testKeyValuesTranslatedMutatorMethods()
    {
        $kg = $this->getNewKeyGenerator();
        $this->assertNotTrue($kg->hasKeyValuesTranslated());
        $this->assertEquals([ ], $kg->getKeyValuesTranslated());

        $kg->setKeyValuesTranslated('val1', 'val2');
        $this->assertEquals(['val1', 'val2'], $kg->getKeyValuesTranslated());

        $kg->addKeyValuesTranslated('val3');
        $this->assertEquals(['val1', 'val2', 'val3'], $kg->getKeyValuesTranslated());
        $this->assertTrue($kg->hasKeyValuesTranslated());

        $kg->setKeyValuesTranslated();
        $this->assertNotTrue($kg->hasKeyValuesTranslated());
    }

    /**
     * @expectedException        Scribe\CacheBundle\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage A passed translated value was not properly converted to a string.
     */
    public function testKeyValuesTranslatedSetterExceptionHandling()
    {
        $kg = $this
            ->getNewKeyGenerator()
            ->setKeyValuesTranslated((new \stdClass()))
        ;
    }

    /**
     * @expectedException        Scribe\CacheBundle\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage A passed translated value was not properly converted to a string.
     */
    public function testKeyValuesTranslatedAdderExceptionHandling()
    {
        $kg = $this
            ->getNewKeyGenerator()
            ->addKeyValuesTranslated((new \stdClass()))
        ;
    }

    public function testKeyValuesTranslationModeMutatorMethods()
    {
        $kg = $this->getNewKeyGenerator();
        $this->assertEquals(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_DEFAULT, $kg->getKeyValuesTranslationMode());

        $kg->setKeyValuesTranslationMode(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_INTERNAL);
        $this->assertEquals(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_INTERNAL, $kg->getKeyValuesTranslationMode());

        $kg->setKeyValuesTranslationMode(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_CLOSURE);
        $this->assertEquals(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_CLOSURE, $kg->getKeyValuesTranslationMode());
    }

    /**
     * @expectedException        Scribe\CacheBundle\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage An invalid key for values translation mode was detected.
     */
    public function testKeyValuesTranslationModeSetterExceptionHandlingNonInt()
    {
        $kg = $this
            ->getNewKeyGenerator()
            ->setKeyValuesTranslationMode('string')
        ;
    }

    /**
     * @expectedException        Scribe\CacheBundle\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage An invalid key for values translation mode of 123456789 was detected and cannot be used.
     */
    public function testKeyValuesTranslationModeSetterExceptionHandlingInvalidInt()
    {
        $kg = $this
            ->getNewKeyGenerator()
            ->setKeyValuesTranslationMode(123456789)
        ;
    }

    public function testKeyValuesTranslationClosureMutatorMethods()
    {
        $kg = $this->getNewKeyGenerator();
        $this->assertNull($kg->getKeyValuesTranslationClosure());
        $this->assertNotTrue($kg->hasKeyValuesTranslationClosure());

        $expected = $this->getKeyValuesTranslationClosure();
        $kg->setKeyValuesTranslationClosure($expected);
        $this->assertTrue($kg->hasKeyValuesTranslationClosure());
        $this->assertEquals($expected, $kg->getKeyValuesTranslationClosure());
        $this->assertInstanceOf('Closure', $kg->getKeyValuesTranslationClosure());

        $kg->setKeyValuesTranslationClosure(null);
        $this->assertNull($kg->getKeyValuesTranslationClosure());
        $this->assertNotTrue($kg->hasKeyValuesTranslationClosure());
    }

    /**
     * @expectedException             PHPUnit_Framework_Error
     * @expectedExceptionMessageRegex #Argument 1 passed to .* must be an instance of .*, instance of .* given#
     */
    public function testKeyValuesTranslationClosureSetterTypeHint()
    {
        $kg = $this
            ->getNewKeyGenerator()
            ->setKeyValuesTranslationClosure('not-a-closure')
        ;
    }

    public function testKeyHashModeMutatorMethods()
    {
        $kg = $this->getNewKeyGenerator();
        $this->assertEquals(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_DEFAULT, $kg->getKeyHashMode());

        $kg->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_MD5);
        $this->assertEquals(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_MD5, $kg->getKeyHashMode());

        $kg->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_SHA1);
        $this->assertEquals(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_SHA1, $kg->getKeyHashMode());

        $kg->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_SHA1);
        $this->assertEquals(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_SHA1, $kg->getKeyHashMode());

        $kg->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_CLOSURE);
        $this->assertEquals(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_CLOSURE, $kg->getKeyHashMode());
    }

    /**
     * @expectedException        Scribe\CacheBundle\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage An invalid key for hash mode was detected.
     */
    public function testKeyHashModeSetterExceptionHandlingNonInt()
    {
        $kg = $this
            ->getNewKeyGenerator()
            ->setKeyHashMode('string')
        ;
    }

    /**
     * @expectedException        Scribe\CacheBundle\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage An invalid key for hash mode of 123456789 was detected and cannot be used.
     */
    public function testKeyHashModeSetterExceptionHandlingInvalidInt()
    {
        $kg = $this
            ->getNewKeyGenerator()
            ->setKeyHashMode(123456789)
        ;
    }

    public function testKeyHashClosureMutatorMethods()
    {
        $kg = $this->getNewKeyGenerator();
        $this->assertNull($kg->getKeyHashClosure());
        $this->assertNotTrue($kg->hasKeyHashClosure());

        $expected = $this->getKeyHashClosure();
        $kg->setKeyHashClosure($expected);
        $this->assertTrue($kg->hasKeyHashClosure());
        $this->assertEquals($expected, $kg->getKeyHashClosure());
        $this->assertInstanceOf('Closure', $kg->getKeyHashClosure());

        $kg->setKeyHashClosure(null);
        $this->assertNull($kg->getKeyHashClosure());
        $this->assertNotTrue($kg->hasKeyHashClosure());
    }

    /**
     * @expectedException             PHPUnit_Framework_Error
     * @expectedExceptionMessageRegex #Argument 1 passed to .* must be an instance of .*, instance of .* given#
     */
    public function testKeyHashClosureSetterTypeHint()
    {
        $kg = $this
            ->getNewKeyGenerator()
            ->setKeyHashClosure('not-a-closure')
        ;
    }

    public function testCanGenerateKeyShortForm()
    {
        $kg = $this->getNewKeyGenerator();
        $stdClass = new \stdClass();
        $stdClass->desc = 'This is a standard class!';

        $expectedKeyValues = [
            $stdClass,
            'string-value',
            123456789
        ];
        $expectedKeyValuesTranslated = [
            'O:8:"stdClass":1:{s:4:"desc";s:25:"This is a standard class!";}',
            's:12:"string-value";',
            'i:123456789;'
        ];
        $expectedKeyMd5     = 'scribe_cache---f0c6725c36ddad00b38fbcb33091cb42';
        $expectedKeySha1    = 'scribe_cache---3dbd1e522892224feef44c15f133160a3ed9e28e';
        $expectedKeyClosure = 'scribe_cache---4b34db72c9c2ac028722c967f0c3646f5588cf43f3bb59c45254fbfe16295bbd1f5bb54b54a45455d181769a56ab2173fbe0c0605b2996f04655e8443e7f61da';

        $key = $kg->getKey(
            ...$expectedKeyValues
        );

        $this->assertEquals($expectedKeyValues, $kg->getKeyValues());
        $this->assertEquals($expectedKeyValuesTranslated, $kg->getKeyValuesTranslated());
        $this->assertEquals($expectedKeyMd5, $kg->getKey());

        $kg->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_SHA1);
        $this->assertEquals($expectedKeySha1, $kg->getKey());

        $kg->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_CLOSURE);
        $kg->setKeyHashClosure($this->getKeyHashClosure());
        $this->assertEquals($expectedKeyClosure, $kg->getKey());
    }

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
        $expectedKeyValuesTranslated = [
            's:12:"string-value";',
            'i:12345;',
            'O:8:"stdClass":1:{s:4:"desc";s:36:"This is a standard class! The first!";}',
            's:14:"string-value-2";',
            'i:67890;',
            'O:8:"stdClass":1:{s:4:"desc";s:37:"This is a standard class! The second!";}',
        ];
        $expectedKeyMd5     = 'scribe_cache---f3534fbc6095aa262204a1c6d8668cc4';
        $expectedKeySha1    = 'scribe_cache---06578213c350dcfe8cfb5720916bdbfebbb247c7';
        $expectedKeyClosure = 'scribe_cache---2b0068d219630c8b21fc47f999c1bf238e5a38944a9ccaba0300a9dd8c012de736eef3bb74b22e2664b44bb1587f451010d8eebfc4d1293d66836b5f021aa70e';

        $kg = $this->getNewKeyGenerator();
        $key = $kg
            ->setKeyValues(...$firstKeyValues)
            ->addKeyValues(...$secondKeyValues)
            ->setKeyValuesTranslationMode(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_DEFAULT)
            ->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_DEFAULT)
            ->getKey()
        ;

        $this->assertEquals($expectedKeyValues, $kg->getKeyValues());
        $this->assertEquals($expectedKeyValuesTranslated, $kg->getKeyValuesTranslated());
        $this->assertEquals($expectedKeyMd5, $key);
        $this->assertEquals($expectedKeyMd5, $kg->getKey());

        $kg = $this->getNewKeyGenerator();
        $key = $kg
            ->setKeyValues(...$firstKeyValues)
            ->addKeyValues(...$secondKeyValues)
            ->setKeyValuesTranslationMode(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_INTERNAL)
            ->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_DEFAULT)
            ->getKey()
        ;

        $this->assertEquals($expectedKeyValues, $kg->getKeyValues());
        $this->assertEquals($expectedKeyValuesTranslated, $kg->getKeyValuesTranslated());
        $this->assertEquals($expectedKeyMd5, $key);
        $this->assertEquals($expectedKeyMd5, $kg->getKey());

        $kg = $this->getNewKeyGenerator();
        $key = $kg
            ->setKeyValues(...$firstKeyValues)
            ->addKeyValues(...$secondKeyValues)
            ->setKeyValuesTranslationMode(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_CLOSURE)
            ->setKeyValuesTranslationClosure($this->getKeyValuesTranslationClosure())
            ->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_DEFAULT)
            ->getKey()
        ;

        $this->assertEquals($expectedKeyValues, $kg->getKeyValues());
        $this->assertEquals($expectedKeyValuesTranslated, $kg->getKeyValuesTranslated());
        $this->assertEquals($expectedKeyMd5, $key);
        $this->assertEquals($expectedKeyMd5, $kg->getKey());

        $kg = $this->getNewKeyGenerator();
        $key = $kg
            ->setKeyValues(...$firstKeyValues)
            ->addKeyValues(...$secondKeyValues)
            ->setKeyValuesTranslationMode(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_INTERNAL)
            ->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_MD5)
            ->getKey()
        ;

        $this->assertEquals($expectedKeyValues, $kg->getKeyValues());
        $this->assertEquals($expectedKeyValuesTranslated, $kg->getKeyValuesTranslated());
        $this->assertEquals($expectedKeyMd5, $key);
        $this->assertEquals($expectedKeyMd5, $kg->getKey());

        $kg = $this->getNewKeyGenerator();
        $key = $kg
            ->setKeyValues(...$firstKeyValues)
            ->addKeyValues(...$secondKeyValues)
            ->setKeyValuesTranslationMode(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_INTERNAL)
            ->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_SHA1)
            ->getKey()
        ;

        $this->assertEquals($expectedKeyValues, $kg->getKeyValues());
        $this->assertEquals($expectedKeyValuesTranslated, $kg->getKeyValuesTranslated());
        $this->assertEquals($expectedKeySha1, $key);
        $this->assertEquals($expectedKeySha1, $kg->getKey());

        $kg = $this->getNewKeyGenerator();
        $key = $kg
            ->setKeyValues(...$firstKeyValues)
            ->addKeyValues(...$secondKeyValues)
            ->setKeyValuesTranslationMode(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_INTERNAL)
            ->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_CLOSURE)
            ->setKeyHashClosure($this->getKeyHashClosure())
            ->getKey()
        ;

        $this->assertEquals($expectedKeyValues, $kg->getKeyValues());
        $this->assertEquals($expectedKeyValuesTranslated, $kg->getKeyValuesTranslated());
        $this->assertEquals($expectedKeyClosure, $key);
        $this->assertEquals($expectedKeyClosure, $kg->getKey());
    }

    /**
     * @expectedException        Scribe\CacheBundle\Exceptions\RuntimeException
     * @expectedExceptionMessage Could not generate key without any values provided to base the key on.
     */
    public function testHandleKeyValuesTranslationNoKeyValuesExceptionHandling()
    {
        list($kg, $method) = $this->getReflectionForMethod('handleKeyValuesTranslation');

        $kg->setKeyValues();
        $method->invokeArgs($kg, []);
    }

    /**
     * @expectedException        Scribe\CacheBundle\Exceptions\RuntimeException
     * @expectedExceptionMessage Could not handle key values translation during key generation as invalid mode was set.
     */
    public function testHandleKeyValuesTranslationInvalidModeExceptionHandling()
    {
        list($kg, $method, $prop) = $this->getReflectionForMethodAndProperty('handleKeyValuesTranslation', 'keyValuesTranslationMode');

        $kg->setKeyValues('val1', 'val2');
        $prop->setValue($kg, 123456789);
        $method->invokeArgs($kg, []);
    }

    /**
     * @expectedException        Scribe\CacheBundle\Exceptions\RuntimeException
     * @expectedExceptionMessage PHP resources (such as DB connections, file handles, etc) cannot be used as key values using the internal translation method.
     */
    public function testHandleKeyValuesTranslationInternalExceptionHandling()
    {
        $kg = $this
            ->getNewKeyGenerator()
            ->getKey($this->testResource)
        ;
    }

    /**
     * @expectedException        Scribe\CacheBundle\Exceptions\RuntimeException
     * @expectedExceptionMessage Could not handle key value translation as closure mode was set but no closure was defined.
     */
    public function testHandleKeyValuesTranslationClosureExceptionHandling()
    {
        $key = $this
            ->getNewKeyGenerator()
            ->setKeyValues('val1', 'val2')
            ->setKeyValuesTranslationMode(KeyGeneratorInterface::MODE_VALUES_TRANSLATION_METHOD_CLOSURE)
            ->getKey()
        ;
    }

    /**
     * @expectedException        Scribe\CacheBundle\Exceptions\RuntimeException
     * @expectedExceptionMessage Could not generate key without any translated values provided to base the key on.
     */
    public function testHandleKeyValuesTranslatedHashingNoKeyExceptionHandling()
    {
        list($kg, $method) = $this->getReflectionForMethod('handleKeyValuesTranslatedHashing');

        $kg->setKeyValues();
        $method->invokeArgs($kg, []);
    }

    /**
     * @expectedException        Scribe\CacheBundle\Exceptions\RuntimeException
     * @expectedExceptionMessage Could not handle key hashing during key generation as invalid mode was set.
     */
    public function testHandleKeyValuesTranslatedHashingInvalidModeExceptionHandling()
    {
        list($kg, $method, $prop) = $this->getReflectionForMethodAndProperty('handleKeyValuesTranslatedHashing', 'keyHashMode');

        $kg->setKeyValuesTranslated('val1', 'val2');
        $prop->setValue($kg, 123456789);
        $method->invokeArgs($kg, []);
    }

    /**
     * @expectedException        Scribe\CacheBundle\Exceptions\RuntimeException
     * @expectedExceptionMessage Could not handle key hashing as closure mode was set but no closure was defined.
     */
    public function testHandleKeyValuesTranslatedHashingClosureExceptionHandling()
    {
        $key = $this
            ->getNewKeyGenerator()
            ->setKeyValues('val1', 'val2')
            ->setKeyHashMode(KeyGeneratorInterface::MODE_KEY_HASH_METHOD_CLOSURE)
            ->getKey()
        ;
    }

    protected function tearDown()
    {
        fclose($this->testResource);
    }
}

/* EOF */
