<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Command;

use ONGR\ConnectionsBundle\Command\CommandBenchmark;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Unit test for CommandBenchmark.
 */
class CommandBenchmarkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test Command benchmark.
     */
    public function testBenchmark()
    {
        /** @var OutputInterface|MockObject $output */
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $output->expects($this->exactly(3))->method('writeln');
        $output->expects($this->at(0))->method('writeln')->with($this->equalTo(''));
        $output->expects($this->at(1))->method('writeln')->with($this->stringContains('<info>Job finished in '));
        $output->expects($this->at(2))->method('writeln')->with($this->stringContains('<info>Memory usage: '));

        $benchmark = new CommandBenchmark($output);

        $benchmark->start();
        $benchmark->finish();

        // Restart benchmark, to test output statistics array.
        $benchmark->start();
        $data = $benchmark->finish(false);

        $this->assertArrayHasKey('start', $data);
        $this->assertArrayHasKey('finish', $data);
        $this->assertArrayHasKey('duration', $data);
        $this->assertArrayHasKey('memory_peak', $data);
    }
}
