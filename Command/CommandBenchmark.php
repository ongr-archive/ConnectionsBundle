<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Command;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CommandBenchmark to show some benchmark information for command.
 */
class CommandBenchmark
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var float Time in microsecond of operation start.
     */
    private $start;

    /**
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Resets start clock.
     */
    public function start()
    {
        $this->start = microtime(true);
    }

    /**
     * Ends statistics collection and outputs or returns statistics.
     *
     * @param bool $outputStat Wheather to output to standart output, or return data array.
     *
     * @return array
     */
    public function finish($outputStat = true)
    {
        if ($outputStat == true) {
            $this->output->writeln('');
            $this->output->writeln(sprintf('<info>Job finished in %.2f s</info>', microtime(true) - $this->start));
            $this->output->writeln(sprintf('<info>Memory usage: %.2f MB</info>', memory_get_peak_usage() >> 20));
        } else {
            $end = microtime(true);

            return [
                'start' => $this->start,
                'finish' => $end,
                'duration' => $end - $this->start,
                'memory_peak' => memory_get_peak_usage() >> 20,
            ];
        }
    }
}
