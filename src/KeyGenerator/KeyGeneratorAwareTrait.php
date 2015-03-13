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
 * Trait KeyGeneratorAwareTrait.
 */
trait KeyGeneratorAwareTrait
{
    /**
     * An instance of a class implementing KeyGeneratorInterface.
     *
     * @var KeyGeneratorInterface|null
     */
    protected $keyGenerator = null;

    /**
     * Set the key generator instance.
     *
     * @param KeyGeneratorInterface|null $keyGenerator
     *
     * @return $this
     */
    public function setKeyGenerator(KeyGeneratorInterface $keyGenerator = null)
    {
        $this->keyGenerator = $keyGenerator;

        return $this;
    }

    /**
     * Get the key generator instance.
     *
     * @return KeyGeneratorInterface|null
     */
    public function getKeyGenerator()
    {
        return $this->keyGenerator;
    }
}

/* EOF */
