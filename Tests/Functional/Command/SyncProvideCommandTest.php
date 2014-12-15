<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\Command;

use ONGR\ConnectionsBundle\Command\SyncProvideCommand;
use ONGR\ConnectionsBundle\Sync\Extractor\ActionTypes;
use ONGR\ConnectionsBundle\Sync\StorageManager\MysqlStorageManager;
use ONGR\ConnectionsBundle\Tests\Functional\TestBase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class SyncProvideCommandTest extends TestBase
{
    /**
     * Clear logs before each test.
     */
    public function setUp()
    {
        parent::setUp();
        $this->getConnection()->executeQuery('RESET MASTER');
    }

    /**
     * Check if command works. Suppose all operations happened in the same second.
     */
    public function testExecuteWithoutTimeDifference()
    {
        $kernel = self::createClient()->getKernel();
        $container = $kernel->getContainer();

        /** @var MysqlStorageManager $managerMysql */
        $managerMysql = $this->getSyncStorageManager($container);
        $this->importData('ExtractorTest/sample_db_nodelay.sql');

        $expectedData = [
            [
                'type' => ActionTypes::UPDATE,
                'document_type' => 'category',
                'document_id' => 'cat0',
                'status' => '0',
                'shop_id' => null,
            ],
            [
                'type' => ActionTypes::UPDATE,
                'document_type' => 'product',
                'document_id' => 'art0',
                'status' => '0',
                'shop_id' => null,
            ],
            [
                'type' => ActionTypes::UPDATE,
                'document_type' => 'product',
                'document_id' => 'art1',
                'status' => '0',
                'shop_id' => null,
            ],
            [
                'type' => ActionTypes::CREATE,
                'document_type' => 'product',
                'document_id' => 'art0',
                'status' => '0',
                'shop_id' => null,
            ],
            [
                'type' => ActionTypes::CREATE,
                'document_type' => 'product',
                'document_id' => 'art1',
                'status' => '0',
                'shop_id' => null,
            ],
            [
                'type' => ActionTypes::CREATE,
                'document_type' => 'product',
                'document_id' => 'art2',
                'status' => '0',
                'shop_id' => null,
            ],
            [
                'type' => ActionTypes::UPDATE,
                'document_type' => 'product',
                'document_id' => 'art2',
                'status' => '0',
                'shop_id' => null,
            ],
            [
                'type' => ActionTypes::DELETE,
                'document_type' => 'product',
                'document_id' => 'art1',
                'status' => '0',
                'shop_id' => null,
            ],
        ];

        $commandTester = $this->executeCommand($kernel);

        // Ensure that there is no time difference between records (even though there might be).
        $managerMysql->getConnection()->executeQuery("update {$managerMysql->getTableName()} set timestamp=NOW()");

        $storageData = $this->getSyncData($container, count($expectedData));

        $this->assertEquals($expectedData, $storageData);

        $output = $commandTester->getDisplay();
        $this->assertContains('Success.', $output);
    }

    /**
     * Check if command works. There is a difference of one second between insert and update commands.
     */
    public function testExecuteWithTimeDifference()
    {
        $kernel = self::createClient()->getKernel();
        $container = $kernel->getContainer();

        $this->getSyncStorageManager($container);
        $this->importData('ExtractorTest/sample_db.sql');

        $expectedData = [
            [
                'type' => ActionTypes::UPDATE,
                'document_type' => 'category',
                'document_id' => 'cat0',
                'status' => '0',
                'shop_id' => null,
            ],
            [
                'type' => ActionTypes::CREATE,
                'document_type' => 'product',
                'document_id' => 'art0',
                'status' => '0',
                'shop_id' => null,
            ],
            [
                'type' => ActionTypes::CREATE,
                'document_type' => 'product',
                'document_id' => 'art1',
                'status' => '0',
                'shop_id' => null,
            ],
            [
                'type' => ActionTypes::CREATE,
                'document_type' => 'product',
                'document_id' => 'art2',
                'status' => '0',
                'shop_id' => null,
            ],
            [
                'type' => ActionTypes::UPDATE,
                'document_type' => 'product',
                'document_id' => 'art0',
                'status' => '0',
                'shop_id' => null,
            ],
            [
                'type' => ActionTypes::UPDATE,
                'document_type' => 'product',
                'document_id' => 'art1',
                'status' => '0',
                'shop_id' => null,
            ],
            [
                'type' => ActionTypes::UPDATE,
                'document_type' => 'product',
                'document_id' => 'art2',
                'status' => '0',
                'shop_id' => null,
            ],
            [
                'type' => ActionTypes::DELETE,
                'document_type' => 'product',
                'document_id' => 'art1',
                'status' => '0',
                'shop_id' => null,
            ],
        ];

        $commandTester = $this->executeCommand($kernel);

        $storageData = $this->getSyncData($container, count($expectedData));

        $this->assertEquals($expectedData, $storageData);

        $output = $commandTester->getDisplay();
        $this->assertContains('Success.', $output);
    }

    /**
     * Executes ongr:sync:provide command.
     *
     * @param KernelInterface $kernel
     *
     * @return CommandTester
     */
    private function executeCommand($kernel)
    {
        $application = new Application($kernel);
        $application->add(new SyncProvideCommand());
        $command = $application->find('ongr:sync:provide');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                'target' => 'some-target',
            ]
        );

        return $commandTester;
    }

    /**
     * Sets up Sync storage, returns MysqlStorageManager.
     *
     * @param ContainerInterface $container
     *
     * @return MysqlStorageManager
     */
    private function getSyncStorageManager($container)
    {
        $managerMysql = $container->get('ongr_connections.sync.storage_manager.mysql_storage_manager');
        $managerMysql->createStorage();

        return $managerMysql;
    }

    /**
     * Gets data from Panther.
     *
     * @param ContainerInterface $container
     * @param int                $count
     *
     * @return array
     */
    private function getSyncData($container, $count)
    {
        $syncStorage = $container->get('ongr_connections.sync.sync_storage');
        $storageData = $syncStorage->getChunk($count);

        // Remove `id` and `timestamp` from result array.
        array_filter(
            $storageData,
            function (&$var) {
                unset($var['id']);
                unset($var['timestamp']);
            }
        );

        return $storageData;
    }
}
