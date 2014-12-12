<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Sync\SyncStorage\StorageManager;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Table;
use ONGR\ConnectionsBundle\Sync\SyncStorage\StorageManager\MysqlStorageManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Unit test for MysqlStorageManagerTest.
 */
class MysqlStorageManagerTest extends \PHPUnit_Framework_TestCase
{
    const TABLE_NAME = 'panther_storage_test';

    /**
     * @var Connection|MockObject
     */
    private $connection;

    /**
     * @var MysqlStorageManager
     */
    private $service;

    /**
     * Set-up services before each test.
     */
    protected function setUp()
    {
        $this->connection = $this->getMockBuilder('\Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->service = new MysqlStorageManager($this->connection, self::TABLE_NAME);
    }

    /**
     * Test storage creation (when table already exists).
     */
    public function testCreateStorageTableAlreadyExists()
    {
        $shopId = 123;

        $schemaManager = $this->getMockBuilder('\Doctrine\DBAL\Schema\AbstractSchemaManager')
            ->disableOriginalConstructor()
            ->setMethods(['_getPortableTableColumnDefinition', 'tablesExist'])
            ->getMock();
        $schemaManager->expects($this->any())
            ->method('_getPortableTableColumnDefinition');
        $schemaManager->expects($this->once())
            ->method('tablesExist')
            ->with([self::TABLE_NAME . '_' . $shopId])
            ->will($this->returnValue(true));
        $this->connection->expects($this->once())
            ->method('getSchemaManager')
            ->will($this->returnValue($schemaManager));

        $result = $this->service->createStorage($shopId);
        $this->assertTrue($result);
    }

    /**
     * Test storage creation (when table does not exist).
     */
    public function testCreateStorageTableDoesNotExist()
    {
        $shopId = 123;

        $schemaManager = $this->getMockBuilder('\Doctrine\DBAL\Schema\AbstractSchemaManager')
            ->disableOriginalConstructor()
            ->setMethods(['_getPortableTableColumnDefinition', 'tablesExist', 'createTable'])
            ->getMock();
        $schemaManager->expects($this->any())
            ->method('_getPortableTableColumnDefinition');
        $schemaManager->expects($this->once())
            ->method('tablesExist')
            ->with([self::TABLE_NAME . '_' . $shopId])
            ->will($this->returnValue(false));
        $table = $this->buildStorageTable(self::TABLE_NAME . '_' . $shopId);
        $schemaManager->expects($this->once())
            ->method('createTable')
            ->with($table);
        $this->connection->expects($this->once())
            ->method('getSchemaManager')
            ->will($this->returnValue($schemaManager));

        $result = $this->service->createStorage($shopId);
        $this->assertTrue($result);
    }

    /**
     * Test table name generation logic.
     */
    public function testGetTableName()
    {
        $tableNameAssertions = [
            1 => self::TABLE_NAME . '_' . 1,
            2 => self::TABLE_NAME . '_' . 2,
            12345 => self::TABLE_NAME . '_' . 12345,
        ];

        foreach ($tableNameAssertions as $shopId => $expectedTableName) {
            $actualTableName = $this->service->getTableName($shopId);
            $this->assertEquals($expectedTableName, $actualTableName);
        }
    }

    /**
     * Test record removal from storage.
     */
    public function testRemoveRecord()
    {
        $testRecordId = 123;

        $this->connection->expects($this->once())
            ->method('delete')
            ->with(self::TABLE_NAME, ['id' => $testRecordId]);

        $this->service->removeRecord($testRecordId);
    }

    /**
     * Test record removal while connection is malfunctioning.
     */
    public function testRemoveRecordWhileConnectionIsMalfunctioning()
    {
        $testRecordId = 123;

        $this->connection->expects($this->once())
            ->method('delete')
            ->with(self::TABLE_NAME, ['id' => $testRecordId])
            ->will($this->throwException(new \Exception('Connection is not working')));

        $this->service->removeRecord($testRecordId);
    }

    /**
     * Test next records retrieval with invalid parameters.
     */
    public function testGetNextRecords()
    {
        $this->assertSame([], $this->service->getNextRecords(0));
    }

    /**
     * Builds table for test storage.
     *
     * @param string $tableName
     *
     * @return Table
     */
    private function buildStorageTable($tableName)
    {
        $table = new Table($tableName);
        $table->addColumn('id', 'bigint')
            ->setUnsigned(true)
            ->setAutoincrement(true);

        $table->addColumn('type', 'string')
            ->setLength(1)
            ->setComment('C-CREATE(INSERT),U-UPDATE,D-DELETE');

        $table->addColumn('document_type', 'string')
            ->setLength(32);

        $table->addColumn('document_id', 'string')
            ->setLength(32);

        $table->addColumn('timestamp', 'datetime');

        $table->addColumn('status', 'boolean', ['default' => 0])
            ->setComment('0-new,1-inProgress,2-error');

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['type', 'document_type', 'document_id', 'status']);

        return $table;
    }
}
