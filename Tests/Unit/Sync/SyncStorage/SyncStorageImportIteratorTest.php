<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use ONGR\ConnectionsBundle\Sync\ActionTypes;
use ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorage;
use ONGR\ConnectionsBundle\Sync\SyncStorageImportIterator;
use ONGR\ElasticsearchBundle\ORM\Repository;

class SyncStorageImportIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityRepository
     */
    private $entityRepository;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Repository
     */
    private $elasticsearchRepository;

    /**
     * @var SyncStorage
     */
    private $syncStorage;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->entityRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(['find'])
            ->getMock();
        $this->entityRepository->expects($this->once())->method('find')->willReturn(null);

        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(['getRepository'])
            ->getMock();
        $this->entityManager->expects($this->once())->method('getRepository')->willReturn($this->entityRepository);

        $this->elasticsearchRepository = $this->getMockBuilder('ONGR\ElasticsearchBundle\ORM\Repository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->syncStorage = $this->getMockBuilder('ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorage')
            ->disableOriginalConstructor()
            ->setMethods(['getChunk'])
            ->getMock();
    }

    /**
     * Test next method.
     */
    public function testNext()
    {
        $this->syncStorage
            ->expects($this->once())
            ->method('getChunk')
            ->will(
                $this->returnValue(
                    [
                        0 => [
                            'document_id' => 1,
                            'type' => ActionTypes::UPDATE,
                        ],
                    ]
                )
            );

        $iterator = new SyncStorageImportIterator(
            [
                'sync_storage' => $this->syncStorage,
                'shop_id' => 1,
                'document_type' => 'product',
                'document_id' => 777,
            ],
            $this->elasticsearchRepository,
            $this->entityManager,
            'Product'
        );

        $iterator->next();

        $this->assertEquals(0, $iterator->key());
    }

    /**
     * Tests key() method.
     */
    public function testKey()
    {
        $this->syncStorage
            ->expects($this->once())
            ->method('getChunk')
            ->will(
                $this->returnValue(
                    [
                        0 => [
                            'document_id' => 11,
                            'type' => ActionTypes::DELETE,
                        ],
                    ]
                )
            );

        $iterator = new SyncStorageImportIterator(
            [
                'sync_storage' => $this->syncStorage,
                'shop_id' => 1,
                'document_type' => 'product',
            ],
            $this->elasticsearchRepository,
            $this->entityManager,
            'Product'
        );

        $iterator->next();

        $this->assertEquals(11, $iterator->key());
    }
}
