<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit;

use ONGR\ConnectionsBundle\Command\ImportFullCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ImportFullCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test ongr:connections:import behaviour.
     */
    public function testCommand()
    {
        $factory = $this->getMockBuilder('ONGR\ConnectionsBundle\Pipeline\PipelineFactory')
            ->setMethods(['setProgressBar'])
            ->getMock();
        $factory->expects($this->once())->method('setProgressBar')->will($this->returnValue(null));

        $import = $this->getMockBuilder('ONGR\ConnectionsBundle\Pipeline\PipelineStarter')
            ->setMethods(['getPipelineFactory', 'startPipeline', 'setPipelineFactory'])
            ->getMock();

        $import->setPipelineFactory($factory);

        $import
            ->expects($this->once())
            ->method('getPipelineFactory')
            ->will($this->returnValue($factory));

        $container = new ContainerBuilder();
        $container->set('ongr_connections.import_service', $import);
        $command = new ImportFullCommand();
        $command->setContainer($container);
        $application = new Application();
        $application->add($command);
        $commandForTesting = $application->find('ongr:import:full');
        $commandTester = new CommandTester($commandForTesting);
        $commandTester->execute(
            [
                'command' => $commandForTesting->getName(),
            ]
        );
    }

    /**
     * Test ongr:connections:import behaviour with $target parameter.
     */
    public function testCommandWithTargetParameter()
    {
        $factory = $this->getMockBuilder('ONGR\ConnectionsBundle\Pipeline\PipelineFactory')
            ->setMethods(['setProgressBar'])
            ->getMock();
        $factory->expects($this->once())->method('setProgressBar')->will($this->returnValue(null));

        $initialSync = $this->getMockBuilder('ONGR\ConnectionsBundle\Pipeline\PipelineStarter')
            ->setMethods(['getPipelineFactory', 'startPipeline', 'setPipelineFactory'])
            ->getMock();

        $initialSync->setPipelineFactory($factory);

        $initialSync
            ->expects($this->once())
            ->method('getPipelineFactory')
            ->will($this->returnValue($factory));

        $container = new ContainerBuilder();
        $container->set('ongr_connections.import_service', $initialSync);
        $command = new ImportFullCommand();
        $command->setContainer($container);
        $application = new Application();
        $application->add($command);
        $commandForTesting = $application->find('ongr:import:full');
        $commandTester = new CommandTester($commandForTesting);
        $commandTester->execute(
            [
                'command' => $commandForTesting->getName(),
                'target' => ['test'],
            ]
        );
    }
}
