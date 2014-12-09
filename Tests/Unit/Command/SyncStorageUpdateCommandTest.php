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

use ONGR\ConnectionsBundle\Command\SyncStorageCreateCommand;
use ONGR\ConnectionsBundle\Sync\Panther\Panther;
use ONGR\ConnectionsBundle\Sync\Panther\StorageManager\MysqlStorageManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SyncStorageUpdateCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for testCommand().
     *
     * @return array[]
     */
    public function getTestCommandData()
    {
        return [
            [true, 'successfully created'],
            [null, 'already exists'],
            [false, 'Failed to create'],
        ];
    }

    /**
     * Test ongr:panther:init behavior.
     *
     * @param mixed  $result
     * @param string $message
     *
     * @dataProvider getTestCommandData()
     */
    public function testCommand($result, $message)
    {
        $storageManager = $this->getMockBuilder(
            'ONGR\ConnectionsBundle\Sync\Panther\StorageManager\MysqlStorageManager'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $storageManager->expects($this->once())->method('createStorage')->willReturn($result);

        $container = new ContainerBuilder();
        $container->set('ongr_connections.sync.panther.storage_manager.mysql_storage_manager', $storageManager);
        $container->set('doctrine.dbal.default_connection', $this->getMock('Doctrine\DBAL\Driver\Connection'));

        $command = new SyncStorageCreateCommand();
        $command->setContainer($container);

        $application = new Application();
        $application->add($command);
        $commandForTesting = $application->find('ongr:sync:storage:create');
        $commandTester = new CommandTester($commandForTesting);
        $commandTester->execute(
            [
                'command' => $commandForTesting->getName(),
                'storage' => Panther::STORAGE_MYSQL,
            ]
        );

        $this->assertContains($message, $commandTester->getDisplay());
    }

    /**
     * Test invalid storage.
     */
    public function testInvalidStorageCommand()
    {
        $invalidStorageName = 'some-invalid-storage';

        /** @var MysqlStorageManager|MockObject $storageManager */
        $storageManager = $this->getMockBuilder(
            'ONGR\ConnectionsBundle\Sync\Panther\StorageManager\MysqlStorageManager'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $storageManager->expects($this->never())->method('createTable');

        $container = new ContainerBuilder();
        $container->set('ongr_connections.sync.panther.storage_manager.mysql_storage_manager', $storageManager);
        $container->set('doctrine.dbal.default_connection', $this->getMock('Doctrine\DBAL\Driver\Connection'));

        $command = new SyncStorageCreateCommand();
        $command->setContainer($container);

        $application = new Application();
        $application->add($command);
        $commandForTesting = $application->find('ongr:sync:storage:create');

        $this->setExpectedException(
            'InvalidArgumentException',
            "Storage \"$invalidStorageName\" is not implemented yet."
        );

        $commandTester = new CommandTester($commandForTesting);
        $commandTester->execute(
            [
                'command' => $commandForTesting->getName(),
                'storage' => $invalidStorageName,
            ]
        );
    }
}
