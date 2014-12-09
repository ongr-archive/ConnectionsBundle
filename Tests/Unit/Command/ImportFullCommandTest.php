<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Event;

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
        $import = $this->getMockBuilder('ONGR\ConnectionsBundle\Command\ImportCommand')
            ->setMethods(['import'])
            ->getMock();
        $import->expects($this->once())->method('import')->will($this->returnValue(null));
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
        $initialSync = $this->getMockBuilder('ONGR\ConnectionsBundle\Command\ImportCommand')
            ->setMethods(['import'])
            ->getMock();
        $initialSync->expects($this->once())->method('import')->with(['test'])->will($this->returnValue(null));

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
