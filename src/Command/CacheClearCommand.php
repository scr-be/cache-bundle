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

namespace Scribe\Teavee\ObjectCacheBundle\Command;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Scribe\Teavee\ObjectCacheBundle\Component\Cache\CacheAttendantInterface;
use Scribe\Teavee\ObjectCacheBundle\Component\Generator\KeyGeneratorInterface;
use Scribe\Teavee\ObjectCacheBundle\Component\Manager\CacheManager;
use Scribe\Teavee\ObjectCacheBundle\DependencyInjection\Compiler\Registrar\CacheCompilerRegistrar;
use Scribe\Wonka\Utility\ClassInfo;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CacheClearCommand
 */
class CacheClearCommand extends ContainerAwareCommand
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('teavee:object-cache:flush')
            ->setDescription('Flush all cache entries from object cache registrar(s).')
            ->setDefinition([
                new InputOption('all', null, InputOption::VALUE_NONE, 'Flush all cache attendants.'),
                new InputOption('list', ['l'], InputOption::VALUE_NONE, 'List registered cache attendants. Adjust verbosity for more info.'),
                new InputOption('attendant', ['a'], InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, 'Flush specific cache attendants.')
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->io = new SymfonyStyle($input, $output);

        if ($input->getOption('list') === true) {
            return $this->listAttendants();
        }

        $flushAll = $input->getOption('all');
        $flushAtt = $input->getOption('attendant');
        $asAll = $this->getCacheAttendants();
        $asAtt = $this->getCacheAttendants($flushAtt);

        if ($flushAll === true && count($flushAtt) > 0) {
            return $this->error('You cannot specify to both flush all and flush specific registrars.');
        }

        $as = $flushAll === true ? $asAll : $asAtt;

        return $this->doFlush($as);
    }

    /**
     * @param CacheAttendantInterface[] $as
     *
     * @return int
     */
    protected function doFlush(array $as)
    {
        $this->io->section('Flushing all cache entries');

        $rows = [];
        foreach ($as as $a) {
            $result = $a->flush();
            $class = get_class($a);

            $rows[] = [
                $this->output->isVerbose() ? $class : ClassInfo::getClassName($class),
                sprintf(
                    '<fg=%s;options=bold>%s</>',
                    $result ? 'green' : 'red',
                    $result ? ('\\' === DIRECTORY_SEPARATOR ? 'OK' : '✔') : ('\\' === DIRECTORY_SEPARATOR ? 'ERROR' : '✘')
                )
            ];
        }

        $this->io->table(['Attendant', 'Result'], $rows);

        return 0;
    }

    /**
     * @return int
     */
    protected function listAttendants()
    {
        $attendants = array_map(function(CacheAttendantInterface $a) {
            return ClassInfo::getClassNameByInstance($a);
        }, $this->getCacheAttendants([], true));

        $header = ['Cache Registrar Name', 'Enabled', 'Initialized'];

        if ($this->output->isVerbose()) {
            $header = array_merge($header, ['TTL', 'Key Prefix', 'Hash Algo', 'Item Count']);
        }

        $rows = [];

        foreach ($attendants as $i => $name) {
            $instance = $this->getCacheAttendants([$name])[0];

            $rows[$i] = [
                $name,
                $instance->isEnabled() ? 'Yes' : '<fg=white>No</>',
                $instance->isInitialized() ? 'Yes' : '<fg=white>No</>',
            ];

            if (!$this->output->isVerbose()) {
                continue;
            }

            $ttl = $this->getAttendantTtl($instance);
            $gen = $this->getAttendantKeyGenerator($instance);

            try {
                $num = count($instance->listKeys());
            } catch (\Exception $e) {
                $num = '<fg=white>n/a</>';
            }

            $rows[$i] = array_merge($rows[$i], [
                $ttl === 0 ? '<fg=white>0</>' : $ttl,
                $gen->getPrefix(),
                $gen->getAlgorithm(),
                $num,
            ]);
        }

        $this->io->section('Cache Registry Attendant Listing');
        $this->io->table($header, $rows);

        return 0;
    }

    /**
     * @return CacheManager
     */
    protected function getCache()
    {
        return $this->getContainer()->get('s.cache');
    }

    /**
     * @return CacheCompilerRegistrar
     */
    protected function getCacheRegistrar()
    {
        $cm = $this->getCache();

        $registrar = (new \ReflectionClass($cm))->getProperty('registrar');
        $registrar->setAccessible(true);

        return $registrar->getValue($cm);
    }

    /**
     * @return CacheAttendantInterface[]
     */
    protected function getCacheAttendants(array $specific = [], $includeDisabled = false)
    {
        $attendants = [];

        foreach ($this->getCacheRegistrar() as $a) {
            if (($a->isEnabled() && count($specific) === 0) ||
                in_array(ClassInfo::getClassNameByInstance($a), $specific) ||
                $includeDisabled === true
            ) {
                $attendants[] = $a;
            }
        }

        return $attendants;
    }

    /**
     * @param CacheAttendantInterface $a
     *
     * @return int
     */
    protected function getAttendantTtl(CacheAttendantInterface $a)
    {
        $attendant = new \ReflectionClass($a);
        $ttl = $attendant->getProperty('ttl');
        $ttl->setAccessible(true);

        return $ttl->getValue($a);
    }

    /**
     * @param CacheAttendantInterface $a
     *
     * @return KeyGeneratorInterface
     */
    protected function getAttendantKeyGenerator(CacheAttendantInterface $a)
    {
        $attendant = new \ReflectionClass($a);
        $keyGenerator = $attendant->getProperty('keyGenerator');
        $keyGenerator->setAccessible(true);

        return $keyGenerator->getValue($a);
    }

    /**
     * @param string $message
     * @param int    $return
     *
     * @return int
     */
    protected function error($message, $return = 255)
    {
        $this->io->error($message);

        return $return;
    }
}

/* EOF */
