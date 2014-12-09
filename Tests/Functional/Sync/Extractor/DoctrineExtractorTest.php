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

use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\CreateDiffItem;
use ONGR\ConnectionsBundle\Sync\Panther\PantherInterface;
use ONGR\ConnectionsBundle\Tests\Functional\TestBase;

class DoctrineExtractorTest extends TestBase
{
    /**
     * Test extraction service to insert updates to Panther.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testExtract()
    {
        $container = $this->getServiceContainer();
        /** @var \ONGR\ConnectionsBundle\Sync\Extractor\DoctrineExtractor $extractor */
        $extractor = $container->get('ongr_connections.sync.doctrine_extractor');

        // Populate database with schema and data.

        $this->importData('ExtractorTest/sample_db.sql');

        // Get storage mock.

        /** @var PantherInterface|\PHPUnit_Framework_MockObject_MockObject $dummyPanther */
        $dummyPanther = $this->getMock('\ONGR\ConnectionsBundle\Sync\Panther\PantherInterface');
        $dummyPanther
            ->expects($this->exactly(3))
            ->method('save')
            ->withConsecutive(
                ['C', 'category', 'cat0', $this->isInstanceOf('\DateTime')],
                ['U', 'product', 'art0', $this->isInstanceOf('\DateTime')],
                ['U', 'product', 'art1', $this->isInstanceOf('\DateTime')]
            );

        $extractor->setStorageFacility($dummyPanther);

        // Execute.

        $item = new CreateDiffItem();
        $item->setCategory('oxcategories');
        $item->setItem(['OXID' => 'cat0']);
        $item->setTimestamp(new \DateTime());
        $extractor->extract($item);
    }
}
