<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\Sync\SyncStorage\StorageManager;

use DateTime;
use ONGR\ConnectionsBundle\Sync\StorageManager\MysqlStorageManager;
use ONGR\ConnectionsBundle\Tests\Functional\TestBase;

class MysqlStorageManagerTest extends TestBase
{
    const TABLE_NAME = 'sync_storage_test_storage';

    /**
     * @var MysqlStorageManager
     */
    private $service;

    /**
     * Set-up services before executing tests.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->service = new MysqlStorageManager($this->getConnection(), self::TABLE_NAME);
    }

    /**
     * Test possible SQL injection.
     */
    public function testInvalidTableName()
    {
        $tableName = ';SQL injection here';

        $this->setExpectedException('\InvalidArgumentException', "Invalid table name specified: \"$tableName\"");
        $service = new MysqlStorageManager($this->getConnection(), $tableName);
        $service->getTableName();
    }

    /**
     * Test storage space creation.
     */
    public function testCreateStorage()
    {
        $this->service->createStorage(15);
        $this->assertTrue($this->getConnection()->getSchemaManager()->tablesExist([self::TABLE_NAME . '_' . 15]));
        $this->assertFalse($this->getConnection()->getSchemaManager()->tablesExist([self::TABLE_NAME]));

        $this->service->createStorage();
        $this->assertTrue($this->getConnection()->getSchemaManager()->tablesExist([self::TABLE_NAME]));
    }

    /**
     * Test add record functionality.
     */
    public function testAddRecordWorks()
    {
        $expected = [
            (object)[
                'operationType' => 'c',
                'documentType' => 'product',
                'documentId' => 123,
                'dateTime' => new DateTime('NOW -5 minutes'),
            ],
        ];

        $this->service->createStorage();
        $this->addRecords($expected);

        $actual = (object)$this->getConnection()->fetchAssoc(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE
                `type` = :operationType
                AND `document_type` = :documentType
                AND `document_id` = :documentId
                AND `status` = :status',
            [
                'operationType' => $expected[0]->operationType,
                'documentType' => $expected[0]->documentType,
                'documentId' => $expected[0]->documentId,
                'status' => 0,
            ]
        );

        $this->assertTrue(!empty($actual->id));
        $this->assertEquals($expected[0]->operationType, $actual->type);
        $this->assertEquals($expected[0]->documentType, $actual->document_type);
        $this->assertEquals($expected[0]->documentId, $actual->document_id);
        $this->assertEquals($expected[0]->dateTime, new DateTime($actual->timestamp));
    }

    /**
     * Test add record functionality with duplicate records.
     */
    public function testAddDuplicateRecords()
    {
        $shopIds = [1, 2, 3];

        $records = [
            (object)[
                'operationType' => 'c',
                'documentType' => 'product',
                'documentId' => 123,
                'dateTime' => new DateTime('NOW -30 minutes'),
                'shopIds' => $shopIds,
            ],
            // Following record should be the remaining one, because it is the newest one.
            (object)[
                'operationType' => 'c',
                'documentType' => 'product',
                'documentId' => 123,
                'dateTime' => new DateTime('NOW -10 minutes'),
                'shopIds' => $shopIds,
            ],
            (object)[
                'operationType' => 'c',
                'documentType' => 'product',
                'documentId' => 123,
                'dateTime' => new DateTime('NOW -20 minutes'),
                'shopIds' => $shopIds,
            ],
            (object)[
                'operationType' => 'u',
                'documentType' => 'product',
                'documentId' => 123,
                'dateTime' => new DateTime('NOW -5 minutes'),
                'shopIds' => $shopIds,
            ],
        ];
        foreach ($shopIds as $shopId) {
            $this->service->createStorage($shopId);
        }
        $this->addRecords($records);

        foreach ($shopIds as $shopId) {
            $tableName = self::TABLE_NAME . '_' . $shopId;
            $actual = (object)$this->getConnection()->fetchAssoc(
                'SELECT * FROM ' . $tableName . ' WHERE
                `type` = :operationType
                AND `document_type` = :documentType
                AND `document_id` = :documentId
                AND `status` = :status',
                [
                    'operationType' => 'c',
                    'documentType' => 'product',
                    'documentId' => 123,
                    'status' => 0,
                ]
            );

            $this->assertTrue(!empty($actual->id));
            $this->assertEquals($records[1]->dateTime, new DateTime($actual->timestamp));
        }
    }

    /**
     * Test record removal functionality.
     */
    public function testRemoveRecord()
    {
        $shopIds = [1, 2, 3];

        $records = [
            (object)[
                'operationType' => 'c',
                'documentType' => 'product',
                'documentId' => 111,
                'dateTime' => new DateTime('NOW -30 minutes'),
                'shopIds' => $shopIds,
            ],
            (object)[
                'operationType' => 'c',
                'documentType' => 'product',
                'documentId' => 112,
                'dateTime' => new DateTime('NOW -10 minutes'),
                'shopIds' => $shopIds,
            ],
            (object)[
                'operationType' => 'c',
                'documentType' => 'product',
                'documentId' => 113,
                'dateTime' => new DateTime('NOW -20 minutes'),
                'shopIds' => $shopIds,
            ],
            (object)[
                'operationType' => 'u',
                'documentType' => 'product',
                'documentId' => 114,
                'dateTime' => new DateTime('NOW -5 minutes'),
                'shopIds' => $shopIds,
            ],
        ];
        foreach ($shopIds as $shopId) {
            $this->service->createStorage($shopId);
        }
        $this->addRecords($records);

        foreach ($shopIds as $shopId) {
            $tableName = self::TABLE_NAME . '_' . $shopId;
            $records = $this->getConnection()->fetchAll(
                'SELECT id FROM `' . $tableName . '` WHERE `status` = :status',
                ['status' => 0]
            );
            $recordId = $records[rand(0, count($records) - 1)]['id'];
            $this->service->removeRecord($recordId, [$shopId]);

            $records = $this->getConnection()->fetchAll(
                'SELECT id FROM `' . $tableName . '` WHERE `status` = :status',
                ['status' => 0]
            );
            $actualIds = [];
            foreach ($records as $rec) {
                $actualIds[] = $rec['id'];
            }
            $this->assertNotContains($recordId, $actualIds);
        }
    }

    /**
     * Test record retrieval.
     */
    public function testGetNextRecords()
    {
        $chunkSize = 3;

        $processedRecords = [
            (object)[
                'operationType' => 'c',
                'documentType' => 'product',
                'documentId' => 1234,
                'dateTime' => new DateTime('-1 year'),
            ],
            (object)[
                'operationType' => 'c',
                'documentType' => 'product',
                'documentId' => 1235,
                'dateTime' => new DateTime('-1 year'),
            ],
            (object)[
                'operationType' => 'c',
                'documentType' => 'product',
                'documentId' => 1236,
                'dateTime' => new DateTime('-1 year'),
            ],
        ];
        $this->service->createStorage();
        $this->addRecords($processedRecords);
        $updatedRecords = $this->getConnection()
            ->executeUpdate(
                'UPDATE `' . self::TABLE_NAME . '`
                SET status = :status',
                ['status' => 1]
            );
        $this->assertSame(count($processedRecords), $updatedRecords);

        $unprocessedRecords = [
            (object)[
                'operationType' => 'c',
                'documentType' => 'product',
                'documentId' => 2221,
                'dateTime' => new DateTime('-6 months 11:00'),
            ],
            (object)[
                'operationType' => 'c',
                'documentType' => 'product',
                'documentId' => 2222,
                'dateTime' => new DateTime('-6 months 10:00'),
            ],
            (object)[
                'operationType' => 'c',
                'documentType' => 'product',
                'documentId' => 2223,
                'dateTime' => new DateTime('-6 months 09:00'),
            ],
            (object)[
                'operationType' => 'u',
                'documentType' => 'product',
                'documentId' => 2224,
                'dateTime' => new DateTime('-6 months 13:00'),
            ],
            // Previous item gets overwritten by following one.
            (object)[
                'operationType' => 'u',
                'documentType' => 'product',
                'documentId' => 2224,
                'dateTime' => new DateTime('-6 months 14:00'),
            ],
        ];
        $this->addRecords($unprocessedRecords);
        $nextRecords = $this->service->getNextRecords($chunkSize);
        $this->assertSame($chunkSize, count($nextRecords));

        // Test record order.
        $this->assertEquals($unprocessedRecords[2]->documentId, $nextRecords[0]['document_id']);
        $this->assertEquals($unprocessedRecords[2]->dateTime, new DateTime($nextRecords[0]['timestamp']));
        $this->assertEquals($unprocessedRecords[1]->documentId, $nextRecords[1]['document_id']);
        $this->assertEquals($unprocessedRecords[1]->dateTime, new DateTime($nextRecords[1]['timestamp']));
        $this->assertEquals($unprocessedRecords[0]->documentId, $nextRecords[2]['document_id']);
        $this->assertEquals($unprocessedRecords[0]->dateTime, new DateTime($nextRecords[2]['timestamp']));

        // Test retrieval of more records than are available.
        $nextRecords = $this->service->getNextRecords(136524);
        $this->assertEquals(1, count($nextRecords));
        $this->assertEquals($unprocessedRecords[4]->documentId, $nextRecords[0]['document_id']);

        $unprocessedRecords = [
            (object)[
                'operationType' => 'c',
                'documentType' => 'product',
                'documentId' => 2225,
                'dateTime' => new DateTime('-6 months 11:00'),
            ],
            (object)[
                'operationType' => 'c',
                'documentType' => 'product',
                'documentId' => 2226,
                'dateTime' => new DateTime('-6 months 10:00'),
            ],
            (object)[
                'operationType' => 'c',
                'documentType' => 'category',
                'documentId' => 311,
                'dateTime' => new DateTime('-6 months 09:00'),
            ],
            (object)[
                'operationType' => 'u',
                'documentType' => 'category',
                'documentId' => 312,
                'dateTime' => new DateTime('-6 months 13:00'),
            ],
            (object)[
                'operationType' => 'u',
                'documentType' => 'category',
                'documentId' => 313,
                'dateTime' => new DateTime('-6 months 14:00'),
            ],
        ];
        $this->addRecords($unprocessedRecords);
        $nextRecords = $this->service->getNextRecords(10, 'product');
        foreach ($nextRecords as $record) {
            $this->assertEquals('product', $record['document_type']);
            $this->assertNull($record['shop_id']);
        }

        $documents = $this->getConnection()->fetchAll(
            'SELECT * FROM `' . self::TABLE_NAME . '`
            WHERE `document_type` = :documentType',
            ['documentType' => 'product']
        );
        foreach ($documents as $record) {
            $this->assertEquals(1, $record['status']);
        }

        $nextRecords = $this->service->getNextRecords(10, 'category');
        $this->assertEquals(3, count($nextRecords));
        foreach ($nextRecords as $record) {
            $this->assertEquals('category', $record['document_type']);
            $this->assertNull($record['shop_id']);
        }
    }

    /**
     * Add data for tests.
     *
     * @param array $recordData
     */
    private function addRecords(array $recordData)
    {
        foreach ($recordData as $idx => $record) {
            $this->service->addRecord(
                $record->operationType,
                $record->documentType,
                $record->documentId,
                $record->dateTime,
                isset($record->shopIds) ? $record->shopIds : null
            );
        }
    }
}
