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
 * Integration test for ongr:sync:storage:init command.
 */
class SyncStorageUpdateCommandTest extends TestBase
{
    /**
     * Check if table is created as expected.
     */
    public function testExecute()
    {
        $testShopId = 14;

        $kernel = self::createClient()->getKernel();

        $application = new Application($kernel);
        $application->add(new SyncStorageCreateCommand());
        $command = $application->find('ongr:sync:storage:create');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                'storage' => SyncStorage::STORAGE_MYSQL,
                '--shop-id' => $testShopId,
            ]
        );

        $this->assertEquals(
            'Storage successfully created for ' . SyncStorage::STORAGE_MYSQL . '.' . PHP_EOL,
            $commandTester->getDisplay()
        );

        $actual = $this->getConnection()->getSchemaManager()->listTableDetails('ongr_sync_storage_storage_' . $testShopId);

        $this->getConnection()->getSchemaManager()->dropTable('ongr_sync_storage_storage_' . $testShopId);
        $this->importData('SyncStorage/tableSingle_shop' . $testShopId . '.sql');
        $expected = $this->getConnection()->getSchemaManager()->listTableDetails('ongr_sync_storage_storage_' . $testShopId);

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
}
