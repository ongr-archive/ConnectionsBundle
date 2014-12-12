<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ConnectionsBundle\Tests\Functional\Sync\Extractor;

use DateTime;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\CreateDiffItem;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\DeleteDiffItem;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\UpdateDiffItem;
use ONGR\ConnectionsBundle\Sync\Extractor\PassthroughExtractor;
use ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorage;
use ONGR\ConnectionsBundle\Sync\SyncStorage\StorageManager\MysqlStorageManager;
use ONGR\ConnectionsBundle\Tests\Functional\TestBase;

class PassthroughExtractorTest extends TestBase
{
    const TABLE_NAME = 'data_sync_test_storage';

    /**
     * @var PassthroughExtractor
     */
    private $extractor;

    /**
     * @var SyncStorage
     */
    private $syncStorage;

    /**
     * @var MysqlStorageManager
     */
    private $storageManager;

    /**
     * Setup services for tests.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->storageManager = new MysqlStorageManager($this->getConnection(), self::TABLE_NAME);
        $this->syncStorage = new SyncStorage($this->storageManager);
        $this->extractor = new PassthroughExtractor();
        $this->extractor->setStorageFacility($this->syncStorage);
    }

    /**
     * Test if extract is able to add data to the storage for item create action.
     */
    public function testExtractForCreateItem()
    {
        $category = 'product';
        $id = 123;
        $timestamp = new DateTime('-1 hour 20 minutes');

        $this->storageManager->createStorage();

        $createDiffItem = new CreateDiffItem();
        $createDiffItem->setCategory($category);
        $createDiffItem->setItemId($id);
        $createDiffItem->setTimestamp($timestamp);

        $this->extractor->extract($createDiffItem);

        $actual = (object)$this->getConnection()->fetchAssoc(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE
                `type` = :operationType
                AND `document_type` = :documentType
                AND `document_id` = :documentId
                AND `status` = :status',
            [
                'operationType' => 'c',
                'documentType' => $category,
                'documentId' => $id,
                'status' => 0,
            ]
        );
        $this->assertTrue(!empty($actual->id));
        $this->assertEquals('c', $actual->type);
        $this->assertEquals($category, $actual->document_type);
        $this->assertEquals($id, $actual->document_id);
        $this->assertEquals($timestamp, new DateTime($actual->timestamp));
    }

    /**
     * Test if extract is able to add data to the storage for item update action.
     */
    public function testExtractForUpdateItem()
    {
        $category = 'product';
        $id = 123;
        $timestamp = new DateTime('-1 hour 20 minutes');

        $this->storageManager->createStorage();

        $updateDiffItem = new UpdateDiffItem();
        $updateDiffItem->setCategory($category);
        $updateDiffItem->setItemId($id);
        $updateDiffItem->setTimestamp($timestamp);

        $this->extractor->extract($updateDiffItem);

        $actual = (object)$this->getConnection()->fetchAssoc(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE
                `type` = :operationType
                AND `document_type` = :documentType
                AND `document_id` = :documentId
                AND `status` = :status',
            [
                'operationType' => 'u',
                'documentType' => $category,
                'documentId' => $id,
                'status' => 0,
            ]
        );
        $this->assertTrue(!empty($actual->id));
        $this->assertEquals('u', $actual->type);
        $this->assertEquals($category, $actual->document_type);
        $this->assertEquals($id, $actual->document_id);
        $this->assertEquals($timestamp, new DateTime($actual->timestamp));
    }

    /**
     * Test if extract is able to add data to the storage for item delete action.
     */
    public function testExtractForDeleteItem()
    {
        $category = 'product';
        $id = 123;
        $timestamp = new DateTime('-1 hour 20 minutes');

        $this->storageManager->createStorage();

        $deleteDiffItem = new DeleteDiffItem();
        $deleteDiffItem->setCategory($category);
        $deleteDiffItem->setItemId($id);
        $deleteDiffItem->setTimestamp($timestamp);

        $this->extractor->extract($deleteDiffItem);

        $actual = (object)$this->getConnection()->fetchAssoc(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE
                `type` = :operationType
                AND `document_type` = :documentType
                AND `document_id` = :documentId
                AND `status` = :status',
            [
                'operationType' => 'd',
                'documentType' => $category,
                'documentId' => $id,
                'status' => 0,
            ]
        );
        $this->assertTrue(!empty($actual->id));
        $this->assertEquals('d', $actual->type);
        $this->assertEquals($category, $actual->document_type);
        $this->assertEquals($id, $actual->document_id);
        $this->assertEquals($timestamp, new DateTime($actual->timestamp));
    }
}
