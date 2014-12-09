<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Sync;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Table;
use ONGR\ConnectionsBundle\Sync\DiffProvider\SyncJobs\TableManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Unit test for TableManager.
 */
class TableManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return MockObject|Connection
     */
    protected function getConnection()
    {
        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $schemaManager = $this->getMockForAbstractClass(
            'Doctrine\DBAL\Schema\AbstractSchemaManager',
            [],
            '',
            false,
            false,
            false,
            get_class_methods('Doctrine\DBAL\Schema\AbstractSchemaManager')
        );

        $connection->expects($this->any())->method('getSchemaManager')->willReturn($schemaManager);

        return $connection;
    }

    /**
     * Test for createTable().
     */
    public function testCreateTable()
    {
        $service = new TableManager($this->getConnection());
        $this->assertTrue($service->createTable());
    }

    /**
     * Test for createTable() in case table already exists.
     */
    public function testCreateTableExists()
    {
        $connection = $this->getConnection();

        /** @var AbstractSchemaManager|MockObject $schemaManager */
        $schemaManager = $connection->getSchemaManager();
        $schemaManager->expects($this->once())->method('tablesExist')->willReturn(true);

        $service = new TableManager($connection);
        $this->assertNull($service->createTable());
    }

    /**
     * Test for createTable() with a custom connection provided.
     */
    public function testCreateTableCustomConnection()
    {
        /** @var Connection|MockObject $defaultConnection */
        $defaultConnection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();
        $defaultConnection->expects($this->never())
            ->method('getSchemaManager');

        $schemaManager = $this->getMockBuilder('Doctrine\DBAL\Schema\AbstractSchemaManager')
            ->disableOriginalConstructor()
            ->setMethods(get_class_methods('Doctrine\DBAL\Schema\AbstractSchemaManager'))
            ->getMockForAbstractClass();
        $schemaManager->expects($this->once())
            ->method('tablesExist');
        $schemaManager->expects($this->once())
            ->method('createTable');

        /** @var Connection|MockObject $customConnection */
        $customConnection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();
        $customConnection->expects($this->once())
            ->method('getSchemaManager')
            ->willReturn($schemaManager);

        $manager = new TableManager($defaultConnection);
        $this->assertTrue($manager->createTable($customConnection));
    }

    /**
     * Test for updateTable().
     */
    public function testUpdateTable()
    {
        $connection = $this->getConnection();

        /** @var AbstractSchemaManager|MockObject $schemaManager */
        $schemaManager = $connection->getSchemaManager();
        $schemaManager->expects($this->once())->method('tablesExist')->willReturn(true);
        $schemaManager->expects($this->once())->method('listTableDetails')->willReturn(new Table('any_table'));

        // We have different title and fields here.
        $service = new TableManager($connection);
        $this->assertTrue($service->updateTable());
    }

    /**
     * Test for updateTable() in case table does not exist.
     */
    public function testUpdateTableNoTable()
    {
        $service = new TableManager($this->getConnection());
        $this->assertFalse($service->updateTable());
    }

    /**
     * Test for updateTable() with a custom connection provided.
     */
    public function testUpdateTableCustomConnection()
    {
        /** @var Connection|MockObject $defaultConnection */
        $defaultConnection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();
        $defaultConnection->expects($this->never())
            ->method('getSchemaManager');

        $schemaManager = $this->getMockBuilder('Doctrine\DBAL\Schema\AbstractSchemaManager')
            ->disableOriginalConstructor()
            ->setMethods(get_class_methods('Doctrine\DBAL\Schema\AbstractSchemaManager'))
            ->getMockForAbstractClass();
        $schemaManager->expects($this->once())
            ->method('tablesExist')
            ->willReturn(true);
        $schemaManager->expects($this->once())
            ->method('listTableDetails')
            ->willReturn(new Table('any_table'));
        $schemaManager->expects($this->once())
            ->method('alterTable');

        /** @var Connection|MockObject $customConnection */
        $customConnection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();
        $customConnection->expects($this->once())
            ->method('getSchemaManager')
            ->willReturn($schemaManager);

        $manager = new TableManager($defaultConnection);
        $this->assertTrue($manager->updateTable($customConnection));
    }

    /**
     * Test for updateTable() in case there were no differences.
     */
    public function testUpdateTableNoDiff()
    {
        $tableName = 'test_table_name';
        $connection = $this->getConnection();

        /** @var AbstractSchemaManager|MockObject $schemaManager */
        $schemaManager = $connection->getSchemaManager();
        $schemaManager->expects($this->once())->method('tablesExist')->willReturn(true);
        $schemaManager->expects($this->once())->method('listTableDetails')->willReturn(new Table($tableName));

        /** @var TableManager $service */
        $service = $this->getMock(
            'ONGR\ConnectionsBundle\Sync\DiffProvider\SyncJobs\TableManager',
            ['buildTable'],
            [$connection, $tableName]
        );

        $this->assertNull($service->updateTable());
    }

    /**
     * Test for createTable() in case of multi shop (expected multiple `status` fields).
     */
    public function testCreateTableMultiShop()
    {
        $connection = $this->getConnection();

        /** @var AbstractSchemaManager|MockObject $schemaManager */
        $schemaManager = $connection->getSchemaManager();
        $schemaManager->expects($this->once())->method('createTable')->with(
            $this->callback(
                function ($table) {
                    /** @var Table $table */
                    $fieldNames = [];

                    foreach ($table->getColumns() as $column) {
                        $fieldNames[] = $column->getName();
                    }

                    // Test if expected fields are set.
                    $this->assertContains('status_alpha', $fieldNames);
                    $this->assertContains('status_beta', $fieldNames);

                    return true;
                }
            )
        );

        $service = new TableManager($connection, 'test_table_name', ['alpha', 'beta']);
        $this->assertTrue($service->createTable());
    }
}
