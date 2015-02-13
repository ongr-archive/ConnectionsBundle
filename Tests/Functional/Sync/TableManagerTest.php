<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\Sync;

use Doctrine\DBAL\Schema\Table;
use ONGR\ConnectionsBundle\Sync\DiffProvider\SyncJobs\TableManager;
use ONGR\ConnectionsBundle\Tests\Functional\AbstractTestCase;

/**
 * Functional test for TableManager.
 */
class TableManagerTest extends AbstractTestCase
{
    /**
     * Data provider for testDoCleanup().
     *
     * @return array
     */
    public function createTableData()
    {
        // Case #0 single shop.
        $out[] = ['tableSingle.sql'];

        // Case #1 multiple shops.
        $shops = ['shop1', 'shop2', 'shop3'];
        $out[] = ['tableMultiple.sql', $shops];

        return $out;
    }

    /**
     * Test if a proper table is created.
     *
     * @param string $file
     * @param array  $shops
     *
     * @dataProvider createTableData()
     */
    public function testCreateTable($file, array $shops = [])
    {
        $schemaManager = $this->getConnection()->getSchemaManager();
        $tableManager = new TableManager($this->getConnection(), 'jobs_test', $shops);
        $this->importData('TableManagerTest/' . $file);
        $properType = $schemaManager->listTableDetails('jobs_test');
        $schemaManager->dropTable('jobs_test');
        $tableManager->createTable();
        $createdType = $schemaManager->listTableDetails('jobs_test');
        $this->compareTable($properType, $createdType);
    }

    /**
     * Test if table is updated properly.
     */
    public function testUpdateTable()
    {
        $schemaManager = $this->getConnection()->getSchemaManager();
        $tableManager = new TableManager($this->getConnection(), 'jobs_test', ['shop1', 'shop2', 'shop3']);
        $this->importData('TableManagerTest/tableOutdated.sql');
        $tableManager->updateTable();
        $updatedTable = $schemaManager->listTableDetails('jobs_test');
        $schemaManager->dropTable('jobs_test');
        $this->importData('TableManagerTest/tableMultiple.sql');
        $this->compareTable($schemaManager->listTableDetails('jobs_test'), $updatedTable);
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
