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

use ONGR\ConnectionsBundle\Sync\Extractor\ActionTypes;
use ONGR\ConnectionsBundle\Sync\Panther\Panther;
use ONGR\ConnectionsBundle\Sync\Panther\StorageManager\MysqlStorageManager;
use ONGR\ConnectionsBundle\Tests\Functional\TestBase;
use ONGR\ConnectionsBundle\Command\SyncProvideCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

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
     * Check if command works.
     */
    public function testExecute()
    {
        $kernel = self::createClient()->getKernel();
        $container = $kernel->getContainer();

        /** @var MysqlStorageManager $pantherMysql */
        $pantherMysql = $container->get('ongr_connections.sync.panther.storage_manager.mysql_storage_manager');
        $pantherMysql->createStorage();
        $this->importData('ExtractorTest/sample_db.sql');

        $application = new Application($kernel);
        $application->add(new SyncProvideCommand());

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

        $command = $application->find('ongr:sync:provide');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                'target' => 'some-target',
            ]
        );

        /** @var Panther $panther */
        $panther = $container->get('ongr_connections.sync.panther');
        $pantherData = $panther->getChunk(count($expectedData));

        // Remove `id` and `timestamp` from result array.
        array_filter(
            $pantherData,
            function (&$var) {
                unset($var['id']);
                unset($var['timestamp']);
            }
        );

        $this->assertEquals($expectedData, $pantherData);

        $output = $commandTester->getDisplay();
        $this->assertContains('Success.', $output);
    }
}
