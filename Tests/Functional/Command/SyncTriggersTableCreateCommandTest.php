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
use ONGR\ConnectionsBundle\Command\SyncTriggersTableCreateCommand;
use ONGR\ConnectionsBundle\Tests\Functional\TestBase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Command function test.
 */
class SyncTriggersTableCreateCommandTest extends TestBase
{
    /**
     * Check if table is created as expected.
     */
    public function testExecute()
    {
        $kernel = self::createClient()->getKernel();

        $application = new Application($kernel);
        $application->add(new SyncTriggersTableCreateCommand());
        $command = $application->find('ongr:sync:triggers:table-create');

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertEquals('Table successfully created.' . PHP_EOL, $commandTester->getDisplay());

        $actual = $this->getConnection()->getSchemaManager()->listTableDetails('ongr_sync_jobs');

        $this->getConnection()->getSchemaManager()->dropTable('ongr_sync_jobs');
        $this->importData('JobManagerTest/syncJobsTable.sql');
        $expected = $this->getConnection()->getSchemaManager()->listTableDetails('ongr_sync_jobs');

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
