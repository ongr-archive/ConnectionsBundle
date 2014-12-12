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

use ONGR\ConnectionsBundle\Command\SyncProvideCommand;
use ONGR\ConnectionsBundle\Sync\DataSyncService;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SyncProvideCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataSyncService|MockObject
     */
    private $dataSyncService;

    /**
     * Setup services before tests.
     */
    protected function setUp()
    {
        $this->dataSyncService = $this->getMockBuilder('ONGR\ConnectionsBundle\Sync\DataSyncService')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test ongr:sync:storage:create behavior.
     */
    public function testCommand()
    {
        $targetName = 'some-target';

        $this->dataSyncService->expects($this->once())
            ->method('startPipeline')
            ->with($targetName);

        $container = new ContainerBuilder();
        $container->set('ongr_connections.sync.data_sync_service', $this->dataSyncService);

        $command = new SyncProvideCommand();
        $command->setContainer($container);
        $application = new Application();
        $application->add($command);

        $commandForTesting = $application->find('ongr:sync:provide');
        $commandTester = new CommandTester($commandForTesting);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                'target' => $targetName,
            ]
        );

        $this->assertContains('Success.', $commandTester->getDisplay());
    }
}
