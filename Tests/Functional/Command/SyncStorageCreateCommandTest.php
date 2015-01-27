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

use Doctrine\DBAL\Schema\Table;
use ONGR\ConnectionsBundle\Command\SyncStorageCreateCommand;
use ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorage;
use ONGR\ConnectionsBundle\Tests\Functional\TestBase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Integration test for ongr:sync:storage:create command.
 */
class SyncStorageCreateCommandTest extends TestBase
{
    /**
     * @var \Symfony\Component\Console\Command\Command
     */
    private $executeCommand;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->setExecuteCommandInstance();
    }

    /**
     * Check if table with shop id was created as expected.
     */
    public function testExecuteWithShopId()
    {
        $testShopId = 14;

        $commandTester = new CommandTester($this->executeCommand);
        $commandTester->execute(
            [
                'command' => $this->executeCommand->getName(),
                'storage' => SyncStorage::STORAGE_MYSQL,
                '--shop-id' => $testShopId,
            ]
        );

        $this->assertEquals(
            'Storage successfully created for ' . SyncStorage::STORAGE_MYSQL . '.' . PHP_EOL,
            $commandTester->getDisplay()
        );

        $actual = $this->getConnection()->getSchemaManager()->listTableDetails(
            'ongr_sync_storage_' . $testShopId
        );

        $this->getConnection()->getSchemaManager()->dropTable('ongr_sync_storage_' . $testShopId);
        $this->importData('SyncStorage/tableSingle_shop' . $testShopId . '.sql');
        $expected = $this->getConnection()->getSchemaManager()->listTableDetails(
            'ongr_sync_storage_' . $testShopId
        );

        $this->compareTable($expected, $actual);
    }

    /**
     * Check if table without shop id was created as expected.
     */
    public function testExecuteWithoutShopId()
    {
        $defaultStorage = 'ongr_sync_storage';

        $commandTester = new CommandTester($this->executeCommand);
        $commandTester->execute(
            [
                'command' => $this->executeCommand->getName(),
                'storage' => SyncStorage::STORAGE_MYSQL,
            ]
        );

        $this->assertEquals(
            'Storage successfully created for ' . SyncStorage::STORAGE_MYSQL . '.' . PHP_EOL,
            $commandTester->getDisplay()
        );

        $actual = $this->getConnection()->getSchemaManager()->listTableDetails($defaultStorage);

        $this->getConnection()->getSchemaManager()->dropTable($defaultStorage);
        $this->importData('SyncStorage/storageWithoutShop.sql');
        $expected = $this->getConnection()->getSchemaManager()->listTableDetails($defaultStorage);

        $this->compareTable($expected, $actual);
    }

    /**
     * Tests whether both tables are equal.
     *
     * @param Table $expected
     * @param Table $actual
     */
    protected function compareTable(Table $expected, Table $actual)
    {
        $this->assertEquals(
            $expected->getColumns(),
            $actual->getColumns()
        );
        $this->assertEquals(
            $expected->getOptions(),
            $actual->getOptions()
        );
    }

    /**
     * Prepare and set instance of execute command.
     */
    private function setExecuteCommandInstance()
    {
        $kernel = self::createClient()->getKernel();

        $application = new Application($kernel);
        $application->add(new SyncStorageCreateCommand());

        $this->executeCommand = $application->find('ongr:sync:storage:create');
    }
}
