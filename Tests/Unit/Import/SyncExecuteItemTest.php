<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Event;

use ONGR\ConnectionsBundle\Pipeline\Item\SyncExecuteItem;
use ONGR\ConnectionsBundle\Sync\ActionTypes;
use ONGR\ConnectionsBundle\Tests\Functional\Fixtures\Bundles\Acme\TestBundle\Document\Product;
use ONGR\ConnectionsBundle\Tests\Functional\Fixtures\ImportCommandTest\TestProduct;

class SyncExecuteItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test SyncExecuteItem getter and setter.
     */
    public function testSyncStorageDataGetterSetter()
    {
        $entity = new TestProduct();
        $document = new Product();
        $syncStorageData = [];
        $syncImportItem = new SyncExecuteItem($entity, $document, $syncStorageData);
        $syncStorageData = [
            'id' => '1',
            'type' => ActionTypes::CREATE,
            'document_type' => 'product',
        ];
        $syncImportItem->setSyncStorageData($syncStorageData);
        $result = $syncImportItem->getSyncStorageData();
        $this->assertEquals($syncStorageData, $result);
    }
}
