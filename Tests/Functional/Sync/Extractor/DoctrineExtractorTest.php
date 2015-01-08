<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\Sync\Extractor;

use ONGR\ConnectionsBundle\Sync\ActionTypes;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\CreateDiffItem;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\UpdateDiffItem;
use ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorageInterface;
use ONGR\ConnectionsBundle\Tests\Functional\TestBase;

class DoctrineExtractorTest extends TestBase
{
    /**
     * Test extraction service to insert updates to SyncStorage.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testExtract()
    {
        $container = $this->getServiceContainer();
        /** @var \ONGR\ConnectionsBundle\Sync\Extractor\DoctrineExtractor $extractor */
        $extractor = $container->get('ongr_connections.sync.extractor.doctrine_extractor');

        // Populate database with schema and data.

        $this->importData('ExtractorTest/sample_db.sql');

        // Get storage mock.

        /** @var SyncStorageInterface|\PHPUnit_Framework_MockObject_MockObject $dummySyncStorage */
        $dummySyncStorage = $this->getMock('\ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorageInterface');
        $dummySyncStorage
            ->expects($this->exactly(3))
            ->method('save')
            ->withConsecutive(
                [ActionTypes::UPDATE, 'category', 'cat0', $this->isInstanceOf('\DateTime')],
                [ActionTypes::UPDATE, 'product', 'art0', $this->isInstanceOf('\DateTime')],
                [ActionTypes::UPDATE, 'product', 'art1', $this->isInstanceOf('\DateTime')]
            );

        $extractor->setStorageFacility($dummySyncStorage);

        // Execute.

        // Should not make any save calls because Category CREATE action is turned off.
        $item = new CreateDiffItem();
        $item->setCategory('oxcategories');
        $item->setItem(['OXID' => 'cat0', 'OXTITLE' => 'Category']);
        $item->setTimestamp(new \DateTime());
        $extractor->extract($item);

        // Should not make any saves because no tracked field updated.
        $item = new UpdateDiffItem();
        $item->setCategory('oxcategories');
        $item->setItem(['OXID' => 'cat0', 'OXTITLE' => 'Category']);
        $item->setOldItem(['OXID' => 'cat0', 'OXTITLE' => 'Category']);
        $item->setTimestamp(new \DateTime());
        $extractor->extract($item);

        // Should make 3 save calls.
        $item->setItem(['OXID' => 'cat0', 'OXTITLE' => 'CategoryNew']);
        $extractor->extract($item);
    }
}
