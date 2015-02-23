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
use ONGR\ConnectionsBundle\Sync\StorageManager\MysqlStorageManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Unit test for MysqlStorageManagerTest.
 */
class MysqlStorageManagerTest extends \PHPUnit_Framework_TestCase
{
    const TABLE_NAME = 'sync_storage_test';

    /**
     * @var Connection|MockObject
     */
    private $connection;

    /**
     * @var MysqlStorageManager
     */
    private $service;

    /**
     * @var ContainerInterface|MockObject
     */
    private $container;

    /**
     * Set-up services before each test.
     */
    protected function setUp()
    {
        $this->connection = $this->getMockBuilder('\Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->service = new MysqlStorageManager($this->connection, self::TABLE_NAME);

        $this->container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');

        $this->container->expects($this->any())->method('getParameter')->will(
            $this->returnCallback(
                function ($parameter) {
                    if ($parameter == 'ongr_connections.active_shop') {
                        return 'test_default';
                    } elseif ($parameter == 'ongr_connections.shops') {
                        return [
                            'test_default' => ['shop_id' => 0],
                            'test' => ['shop_id' => 1],
                            'test2' => ['shop_id' => 2],
                            'test12345' => ['shop_id' => 12345],
                            'string_id' => ['shop_id' => 'string'],
                            'inject' => ['shop_id' => '\';inject'],
                        ];
                    }

                    return null;
                }
            )
        );

        $this->service->setContainer($this->container);
    }

    /**
     * Test storage creation (when table already exists).
     */
    public function testCreateStorageTableAlreadyExists()
    {
        $shopId = 1;

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
        $shopId = 0;

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
            'string' => self::TABLE_NAME . '_string',
        ];

        foreach ($tableNameAssertions as $shopId => $expectedTableName) {
            $actualTableName = $this->service->getTableName($shopId);
            $this->assertEquals($expectedTableName, $actualTableName);
        }
    }

    /**
     * Tests exception with invalid id.
     */
    public function testGetTableNameException()
    {
        $this->setExpectedException('InvalidArgumentException', 'Shop id "invalid_id" is invalid.');

        $this->service->getTableName('invalid_id');
    }

    /**
     * Test record removal from storage.
     */
    public function testRemoveRecord()
    {
        $testRecordId = 123;

        $this->connection->expects($this->once())
            ->method('delete')
            ->with(self::TABLE_NAME . '_0', ['id' => $testRecordId]);

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
            ->with(self::TABLE_NAME . '_0', ['id' => $testRecordId])
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

    /**
     * Test possible SQL injection in shop id.
     */
    public function testInvalidShopId()
    {
        $id = '\';inject';
        $this->setExpectedException('InvalidArgumentException', "Shop id \"{$id}\" is invalid.");
        $this->service->getTableName($id);
    }
}
