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
use Symfony\Component\DependencyInjection\Container;
use ONGR\ConnectionsBundle\Service\PairStorage;
use \DateTime;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Binlog\BinlogDiffProvider;

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

        $this->setLastSyncDate($container, new DateTime('now'));

        /** @var MysqlStorageManager $managerMysql */
        $managerMysql = $container->get('ongr_connections.sync.storage_manager.mysql_storage_manager');
        $managerMysql->createStorage();

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
        $this->assertContains('Job finished', $output);
    }

    /**
     * Check if command works. There is a difference of one second between insert and update commands.
     */
    public function testExecuteWithTimeDifference()
    {
        $kernel = self::createClient()->getKernel();
        $container = $kernel->getContainer();

        $this->setLastSyncDate($container, new DateTime('now'));

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
        $this->assertContains('Job finished', $output);
    }

    /**
     * Check if command works. Suppose some data is skipped, by using last sync date.
     */
    public function testExecuteSkipDataByLastSyncDate()
    {
        $kernel = self::createClient()->getKernel();
        $container = $kernel->getContainer();

        $this->importData('ExtractorTest/sample_db_to_skip.sql');

        $this->setLastSyncDate($container, new DateTime('now'));

        $this->importData('ExtractorTest/sample_db_to_use.sql');

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
        $this->assertContains('Job finished', $output);
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
     * Gets data from Sync storage.
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

    /**
     * Sets last_sync_date in bin log format.
     *
     * @param Container $container
     * @param \DateTime $date
     */
    private function setLastSyncDate($container, $date)
    {
        /** @var PairStorage $pairStorage */
        $pairStorage = $container->get('ongr_connections.pair_storage');

        // Sometimes, mysql, php and server timezone could differ, we need convert time seen by php
        // to the same time in the same timezone as is used in mysqlbinlog.
        // This issue is for tests only, should not affect live website.
        /** @var MysqlStorageManager $managerMysql */
        $managerMysql = $container->get('ongr_connections.sync.storage_manager.mysql_storage_manager');
        $managerMysql->createStorage();

        $result = $managerMysql->getConnection()->executeQuery('SELECT @@global.time_zone');
        $time_zone = $result->fetchAll()[0]['@@global.time_zone'];

        // If mysql timezone is the same as systems, string 'SYSTEM' is returned, which is not what we want.
        if ($time_zone == 'SYSTEM') {
            $result = $managerMysql->getConnection()->executeQuery('SELECT @@system_time_zone');
            $time_zone = $result->fetchAll()[0]['@@system_time_zone'];
        }

        $date->setTimezone(new \DateTimeZone($time_zone));

        $pairStorage->set(BinlogDiffProvider::LAST_SYNC_DATE_PARAM, $date->format('Y-m-d H:i:s'));
    }
}
