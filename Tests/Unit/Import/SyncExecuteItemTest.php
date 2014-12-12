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

use ONGR\ConnectionsBundle\Import\Item\SyncExecuteItem;
use ONGR\ConnectionsBundle\Sync\Panther\PantherInterface;
use ONGR\ConnectionsBundle\Tests\Functional\Fixtures\ImportCommandTest\TestProduct;
use ONGR\TestingBundle\Document\Product;

class SyncExecuteItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test SyncExecuteItem getter and setter.
     *
     * @return void
     */
    public function testPantherDataGetterSetter()
    {
        $entity = new TestProduct();
        $document = new Product();
        $pantherData = [];
        $syncImportItem = new SyncExecuteItem($entity, $document, $pantherData);
        $pantherData = [
            'id' => '1',
            'type' => PantherInterface::OPERATION_CREATE,
            'document_type' => 'product',
        ];
        $syncImportItem->setPantherData($pantherData);
        $result = $syncImportItem->getPantherData();
        $this->assertEquals($pantherData, $result);
    }
}
