<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Sync\Extractor;

use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\CreateDiffItem;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\DeleteDiffItem;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\UpdateDiffItem;
use ONGR\ConnectionsBundle\Sync\Extractor\PassthroughExtractor;
use ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorageInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class PassthroughExtractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SyncStorageInterface|MockObject
     */
    private $storage;

    /**
     * @var PassthroughExtractor
     */
    private $service;

    /**
     * Setup services for tests.
     */
    protected function setUp()
    {
        $this->storage = $this->getMockBuilder('ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorageInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->service = new PassthroughExtractor();
        $this->service->setStorageFacility($this->storage);
    }

    /**
     * Test if extraction for "create" item works.
     */
    public function testExtractingCreateItem()
    {
        $category = 'product';
        $id = 123;
        $timestamp = new \DateTime('-1 hour');

        $diffItem = new CreateDiffItem();
        $diffItem->setCategory($category);
        $diffItem->setTimestamp($timestamp);
        $diffItem->setItemId($id);

        $this->storage->expects($this->once())
            ->method('save')
            ->with('C', $category, $id, $timestamp);

        $this->service->extract($diffItem);
    }

    /**
     * Test if extraction for "update" item works.
     */
    public function testCreatingUpdateItem()
    {
        $category = 'product';
        $id = 123;
        $timestamp = new \DateTime('-1 hour');

        $diffItem = new UpdateDiffItem();
        $diffItem->setCategory($category);
        $diffItem->setTimestamp($timestamp);
        $diffItem->setItemId($id);

        $this->storage->expects($this->once())
            ->method('save')
            ->with('U', $category, $id, $timestamp);

        $this->service->extract($diffItem);
    }

    /**
     * Test if extraction for "delete" item works.
     */
    public function testCreatingDeleteItem()
    {
        $category = 'product';
        $id = 123;
        $timestamp = new \DateTime('-1 hour');

        $diffItem = new DeleteDiffItem();
        $diffItem->setCategory($category);
        $diffItem->setTimestamp($timestamp);
        $diffItem->setItemId($id);

        $this->storage->expects($this->once())
            ->method('save')
            ->with('D', $category, $id, $timestamp);

        $this->service->extract($diffItem);
    }

    /**
     * Test invalid argument.
     */
    public function testInvalidArgument()
    {
        $diffItem = new CreateDiffItem();
        $diffItem->setCategory('some-category');
        $diffItem->setTimestamp(new \DateTime());

        $this->storage->expects($this->never())
            ->method('save');

        $this->setExpectedException('InvalidArgumentException', 'No valid item ID provided.');

        $this->service->extract($diffItem);
    }

    /**
     * Test getStorageFacility.
     */
    public function testGetStorageFacility()
    {
        $this->assertSame($this->storage, $this->service->getStorageFacility());
    }
}
