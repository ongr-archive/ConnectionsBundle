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
use Symfony\Component\HttpKernel\KernelInterface;
use ONGR\ConnectionsBundle\Service\PairStorage;
use \DateTime;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Binlog\BinlogDiffProvider;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Binlog\BinlogParser;

class SyncProvideCommandTest extends TestBase
{
    /**
     * @var MysqlStorageManager
     */
    private $managerMysql;

    /**
     * Clear logs before each test.
     */
    public function setUp()
    {
        parent::setUp();

        $this
            ->getServiceContainer()
            ->get('es.manager')
            ->getConnection()
            ->dropAndCreateIndex();

        $this->getConnection()->executeQuery('RESET MASTER');

        /** @var MysqlStorageManager $managerMysql */
        $this->managerMysql = $this
            ->getServiceContainer()
            ->get('ongr_connections.sync.storage_manager.mysql_storage_manager');
        $this->managerMysql->createStorage();
    }

    /**
     * Check if command works. Suppose all operations happened in the same second.
     */
    public function testExecuteWithoutTimeDifference()
    {
        // Set last sync date, to now.
        $this->setLastSync(new DateTime('now'), BinlogParser::START_TYPE_DATE);

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

        $commandTester = $this->executeCommand(static::$kernel);

        // Ensure that there is no time difference between records (even though there might be).
        $this
            ->managerMysql
            ->getConnection()
            ->executeQuery("update {$this->managerMysql->getTableName()} set timestamp=NOW()");

        $storageData = $this->getSyncData(count($expectedData));

        $this->assertEquals($expectedData, $storageData);

        $output = $commandTester->getDisplay();
        $this->assertContains('Job finished', $output);
    }

    /**
     * Check if command works. There is a difference of one second between insert and update commands.
     */
    public function testExecuteWithTimeDifference()
    {
        // Set last sync date, to now.
        $this->setLastSync(new DateTime('now'), BinlogParser::START_TYPE_DATE);

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

        $commandTester = $this->executeCommand(static::$kernel);

        $storageData = $this->getSyncData(count($expectedData));

        $this->assertEquals($expectedData, $storageData);

        $output = $commandTester->getDisplay();
        $this->assertContains('Job finished', $output);
    }

    /**
     * Check if command works. Suppose some data is skipped, by using last sync date.
     */
    public function testExecuteSkipDataByLastSyncDate()
    {
        // Creating db and some transactions which should not be in final changes log.
        $this->importData('ExtractorTest/sample_db_to_skip.sql');

        // Set last sync date, to now.
        $this->setLastSync(new DateTime('now'), BinlogParser::START_TYPE_DATE);

        // Transactions which should be in changes log.
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

        $commandTester = $this->executeCommand(static::$kernel);

        $storageData = $this->getSyncData(count($expectedData));

        $this->assertEquals($expectedData, $storageData);

        $output = $commandTester->getDisplay();
        $this->assertContains('Job finished', $output);
    }

    /**
     * Check if command works. Suppose some data is skipped, by using last sync position.
     */
    public function testExecuteSkipDataByLastSyncPosition()
    {
        // Set initial start position, if not sure, 0 always returns results.
        $this->setLastSync(0, BinlogParser::START_TYPE_POSITION);
        // Set service so, that it would use last sync position.
        $this
            ->getServiceContainer()
            ->get('ongr_connections.sync.diff_provider.binlog_diff_provider')
            ->setStartType(BinlogParser::START_TYPE_POSITION);

        // Creating db and execute some transactions which should not be in final changes log.
        $this->importData('ExtractorTest/sample_db_to_skip.sql');

        $this->executeCommand(static::$kernel);

        $last_sync_position_1 = $this
            ->getServiceContainer()
            ->get('ongr_connections.pair_storage')
            ->get(BinlogDiffProvider::LAST_SYNC_POSITION_PARAM);
        $this->assertGreaterThan(0, $last_sync_position_1);

        // Delete data from sync storage, to test if only data from last sync position is processed.
        $storageData = $this
            ->getServiceContainer()
            ->get('ongr_connections.sync.sync_storage')
            ->getChunk(8);
        foreach ($storageData as $storageDataItem) {
            $this
                ->getServiceContainer()
                ->get('ongr_connections.sync.sync_storage')
                ->deleteItem($storageDataItem['id']);
        }

        // Execute transactions which should be in changes log.
        $this->importData('ExtractorTest/sample_db_to_use.sql');

        // There is some kind of an undocumented feature/bug, in which if kernel is not rebooted,
        // when executing Command second time from same process, after inserting new data
        // it can't add new data to sync storage table, but only if Command was executed earlier.
        static::bootKernel();
        static::$kernel
            ->getContainer()
            ->get('ongr_connections.sync.diff_provider.binlog_diff_provider')
            ->setStartType(BinlogParser::START_TYPE_POSITION);

        $commandTester = $this->executeCommand(static::$kernel);

        $last_sync_position_2 = $this
            ->getServiceContainer()
            ->get('ongr_connections.pair_storage')
            ->get(BinlogDiffProvider::LAST_SYNC_POSITION_PARAM);

        $this->assertGreaterThan($last_sync_position_1, $last_sync_position_2);

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

        $storageData = $this->getSyncData(count($expectedData));

        $this->assertEquals($expectedData, $storageData);

        $output = $commandTester->getDisplay();

        $this->assertContains('Job finished', $output);
    }

    /**
     * Check if command shows errors when configured not correctly.
     */
    public function testExecuteNoDateSet()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Last sync parameter is not set! To set it,' .
            ' use command: ongr:sync:provide:parameter last_sync_date set [value]'
        );
        $this->executeCommand(static::$kernel);
    }

    /**
     * Check if command shows errors when configured not correctly.
     */
    public function testExecuteNoPositionSet()
    {
        $this
            ->getServiceContainer()
            ->get('ongr_connections.sync.diff_provider.binlog_diff_provider')
            ->setStartType(BinlogParser::START_TYPE_POSITION);
        $this->setExpectedException(
            'InvalidArgumentException',
            'Last sync parameter is not set! To set it,' .
            ' use command: ongr:sync:provide:parameter last_sync_position set [value]'
        );
        $this->executeCommand(static::$kernel);
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
     * @param int $count
     * @param int $skip
     *
     * @return array
     */
    private function getSyncData($count, $skip = 0)
    {
        $syncStorage = $this->getServiceContainer()->get('ongr_connections.sync.sync_storage');
        $syncStorage->getChunk($skip);
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
     * Sets last_sync_date in bin log format or sets last_sync_position.
     *
     * @param \DateTime|int $from
     * @param int           $startType
     */
    private function setLastSync($from, $startType)
    {
        /** @var PairStorage $pairStorage */
        $pairStorage = $this->getServiceContainer()->get('ongr_connections.pair_storage');

        if ($startType == BinlogParser::START_TYPE_DATE) {
            // Sometimes, mysql, php and server timezone could differ, we need convert time seen by php
            // to the same time in the same timezone as is used in mysqlbinlog.
            // This issue is for tests only, should not affect live website.

            $result = $this->managerMysql->getConnection()->executeQuery('SELECT @@global.time_zone');
            $time_zone = $result->fetchAll()[0]['@@global.time_zone'];

            // If mysql timezone is the same as systems, string 'SYSTEM' is returned, which is not what we want.
            if ($time_zone == 'SYSTEM') {
                $result = $this->managerMysql->getConnection()->executeQuery('SELECT @@system_time_zone');
                $time_zone = $result->fetchAll()[0]['@@system_time_zone'];
            }

            $from->setTimezone(new \DateTimeZone($time_zone));

            $pairStorage->set(BinlogDiffProvider::LAST_SYNC_DATE_PARAM, $from->format('Y-m-d H:i:s'));
        } elseif ($startType == BinlogParser::START_TYPE_POSITION) {
            $pairStorage->set(BinlogDiffProvider::LAST_SYNC_POSITION_PARAM, $from);
        }
    }
}
