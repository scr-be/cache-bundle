<?php

/*
 * This file is part of the Teavee Block Manager Bundle.
 *
 * (c) Scribe Inc.     <oss@scr.be>
 * (c) Rob Frawley 2nd <rmf@scr.be>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Scribe\Teavee\ObjectCacheBundle\Tests\Console;

use Scribe\Teavee\ObjectCacheBundle\Command\CacheClearCommand;
use Scribe\Teavee\ObjectCacheBundle\Component\Cache\CacheAttendantInterface;
use Scribe\Teavee\ObjectCacheBundle\Component\Manager\CacheManagerInterface;
use Scribe\WonkaBundle\Utility\TestCase\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class CacheClearCommandTest.
 */
class CacheClearCommandTest extends KernelTestCase
{
    /**
     * @return CacheAttendantInterface
     */
    public function getCacheManager()
    {
        $cache = self::$staticContainer->get('s.cache')->getActive();
        $cache->flush();

        return $cache;
    }

    /**
     * @param string %string
     *
     * @return mixed[]
     */
    public function getData($string = '')
    {
        $data = [];

        for ($i = 0; $i < 100; $i++) {
            $data['key'.$string.$i] = 'value'.$string.$i;
        }

        return $data;
    }

    /**
     * @return CommandTester
     */
    public function getInstances()
    {
        $flushCommand = new CacheClearCommand();
        $flushCommand->setContainer(self::$staticContainer);

        $application = new Application();
        $application->add($flushCommand);

        $command = $application->find('teavee:object-cache:flush');
        $tester = new CommandTester($command);

        return [$this->getCacheManager(), $tester, ['command' => $command->getName()]];
    }

    public function testList()
    {
        list($cache, $command, $call) = $this->getInstances();

        $command->execute(array_merge($call, [
            '--list' => true
        ]));

        self::assertRegExp('{MemcachedAttendant[^a-zA-Z]+Yes}', $command->getDisplay());
        self::assertRegExp('{RedisAttendant[^a-zA-Z]+Yes}', $command->getDisplay());
        self::assertRegExp('{MockAttendant[^a-zA-Z]+No}', $command->getDisplay());
    }

    public function testListWithVerbosity()
    {
        list($cache, $command, $call) = $this->getInstances();

        $command->execute(array_merge($call, [
            '--list' => true
        ]), ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        self::assertRegExp('{TTL}', $command->getDisplay());
        self::assertRegExp('{Key Prefix}', $command->getDisplay());
        self::assertRegExp('{Hash Algo}', $command->getDisplay());
        self::assertRegExp('{Item Count}', $command->getDisplay());
    }

    public function testListItemCount()
    {
        list($cache, $command, $call) = $this->getInstances();

        $cache->flush();

        $command->execute(array_merge($call, [
            '--list' => true,
        ]), ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        preg_match('{MemcachedAttendant\\s+(?:Yes|No)\\s+(?:Yes|No)\\s+[0-9]+\\s+[^\\s]+\\s+[^\\s]+\\s+(?<count>[0-9]+)}', $command->getDisplay(), $matches);

        self::assertTrue(array_key_exists('count', $matches));
        self::assertEquals(0, (int) $matches['count']);

        $data = $this->getData(__METHOD__);
        foreach ($data as $k => $v) {
            $cache->set($v, $k);
        }

        $command->execute(array_merge($call, [
            '--list' => true,
        ]), ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        preg_match('{MemcachedAttendant\\s+(?:Yes|No)\\s+(?:Yes|No)\\s+[0-9]+\\s+[^\\s]+\\s+[^\\s]+\\s+(?<count>[0-9]+)}', $command->getDisplay(), $matches);

        self::assertTrue(array_key_exists('count', $matches));
        self::assertEquals(count($data), (int) $matches['count']);
    }

    public function testFlushAll()
    {
        list($cache, $command, $call) = $this->getInstances();

        foreach ($this->getData(__METHOD__) as $k => $v) {
            $cache->set($v, $k);
        }

        $keysInitial = $cache->listKeys();
        self::assertTrue(count($keysInitial) > 0);

        $command->execute($call);

        self::assertTrue(count($cache->listKeys()) < count($keysInitial));
        self::assertNotEquals($cache->listKeys(), $keysInitial);
    }

    public function testFlushSingleAttendant()
    {
        list($cache, $command, $call) = $this->getInstances();

        foreach ($this->getData(__METHOD__) as $k => $v) {
            $cache->set($v, $k);
        }

        $keysInitial = $cache->listKeys();
        self::assertTrue(count($keysInitial) > 0);

        $command->execute(array_merge($call, [
            '--attendant' => ['MemcachedAttendant']
        ]));

        self::assertTrue(count($cache->listKeys()) < count($keysInitial));
        self::assertNotEquals($cache->listKeys(), $keysInitial);
    }

    public function testFlushMultipleAttendant()
    {
        list($cache, $command, $call) = $this->getInstances();

        foreach ($this->getData(__METHOD__) as $k => $v) {
            $cache->set($v, $k);
        }

        $keysInitial = $cache->listKeys();
        self::assertTrue(count($keysInitial) > 0);

        $command->execute(array_merge($call, [
            '--attendant' => ['MemcachedAttendant', 'RedisAttendant']
        ]));

        self::assertTrue(count($cache->listKeys()) < count($keysInitial));
        self::assertNotEquals($cache->listKeys(), $keysInitial);
    }

    public function testInvalidAllAndSpecificParams()
    {
        list($cache, $command, $call) = $this->getInstances();

        $return = $command->execute(array_merge($call, [
            '--all' => true,
            '--attendant' => ['MemcachedAttendant']
        ]));

        self::assertRegExp('{cannot specify}', $command->getDisplay());
    }
}

/* EOF */
