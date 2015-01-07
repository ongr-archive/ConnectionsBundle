<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\Sync\SyncStorage;

use DateTime;
use ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorage;
use ONGR\ConnectionsBundle\Sync\StorageManager\StorageManagerInterface;
use ONGR\ConnectionsBundle\Tests\Functional\TestBase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Functional test for SyncStorage.
 */
class SyncStorageTest extends TestBase
{
    /**
     * @var StorageManagerInterface|MockObject
     */
    private $storageManager;

    /**
     * @var SyncStorage
     */
    private $service;

    /**
     * Set-up mocks and service before tests.
     */
    protected function setUp()
    {
        $this->storageManager = $this->getMockBuilder(
            'ONGR\ConnectionsBundle\Sync\StorageManager\StorageManagerInterface'
        )->getMock();

        $this->service = new SyncStorage($this->storageManager);

        parent::setUp();
    }

    /**
     * Test save action.
     */
    public function testSyncStorageSaveAction()
    {
        $shopIds = [1, 2, 3];

        $values = [
            // Create action.
            ['C', 'product', 14, new DateTime('now -1 hour'), $shopIds],
            // Update action.
            ['U', 'product', 14, new DateTime('now -1 hour +1 minute'), $shopIds],
            // Delete action.
            ['D', 'product', 14, new DateTime('now -1 hour +2 minutes'), $shopIds],
        ];

        $this->storageManager->expects($this->exactly(3))
            ->method('addRecord')
            ->will($this->returnValueMap($values));

        foreach ($values as $set) {
            $this->service->save($set[0], $set[1], $set[2], $set[3], $set[4]);
        }

        $this->setExpectedException('InvalidArgumentException', 'Invalid parameters specified.');
        $this->service->save('b', 'product', 14, new DateTime('now -1 hour +3 minutes'));
    }

    /**
     * Test delete action.
     */
    public function testSyncStorageDeleteItem()
    {
        $valueMap = [
            [123, [1], null],
            [123, [1, 2], null],
            [0, null, null],
        ];

        $this->storageManager->expects($this->exactly(count($valueMap) - 1))
            ->method('removeRecord')
            ->will($this->returnValueMap($valueMap));
        foreach ($valueMap as $item) {
            $this->service->deleteItem($item[0], $item[1]);
        }
    }

    /**
     * Test getChunk action.
     */
    public function testSyncStorageGetChunk()
    {
        $valueMap = [
            [1, null, null, [1]],
            [2, null, null, [1, 2]],
            [0, null, null, null],
        ];

        $this->storageManager->expects($this->exactly(count($valueMap) - 1))
            ->method('getNextRecords')
            ->will($this->returnValueMap($valueMap));

        foreach ($valueMap as $record) {
            $records = $this->service->getChunk($record[0], $record[1], $record[2]);
            $this->assertEquals(count($record[3]), count($records));
        }
    }
}
