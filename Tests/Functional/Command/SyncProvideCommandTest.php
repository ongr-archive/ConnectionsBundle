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
use ONGR\ConnectionsBundle\Sync\Panther\StorageManager\MysqlStorageManager;
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

        /** @var MysqlStorageManager $pantherMysql */
        $pantherMysql = $this->getPantherStorageManager($container);
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
        $pantherMysql->getConnection()->executeQuery("update {$pantherMysql->getTableName()} set timestamp=NOW()");

        $pantherData = $this->getPantherData($container, count($expectedData));

        $this->assertEquals($expectedData, $pantherData);

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

        $this->getPantherStorageManager($container);
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

        $pantherData = $this->getPantherData($container, count($expectedData));

        $this->assertEquals($expectedData, $pantherData);

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
     * Sets up Panther storage, returns MysqlStorageManager.
     *
     * @param ContainerInterface $container
     *
     * @return MysqlStorageManager
     */
    private function getPantherStorageManager($container)
    {
        $pantherMysql = $container->get('ongr_connections.sync.panther.storage_manager.mysql_storage_manager');
        $pantherMysql->createStorage();

        return $pantherMysql;
    }

    /**
     * Gets data from Panther.
     *
     * @param ContainerInterface $container
     * @param int                $count
     *
     * @return array
     */
    private function getPantherData($container, $count)
    {
        $panther = $container->get('ongr_connections.sync.panther');
        $pantherData = $panther->getChunk($count);

        // Remove `id` and `timestamp` from result array.
        array_filter(
            $pantherData,
            function (&$var) {
                unset($var['id']);
                unset($var['timestamp']);
            }
        );

        return $pantherData;
    }
}
