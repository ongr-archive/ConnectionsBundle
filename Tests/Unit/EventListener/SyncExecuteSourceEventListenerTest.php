<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use ONGR\ConnectionsBundle\EventListener\SyncExecuteSourceEventListener;
use ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorage;
use ONGR\ElasticsearchBundle\ORM\Manager;

class SyncExecuteSourceEventListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * @var Manager
     */
    private $elasticsearchManager;

    /**
     * @var SyncStorage
     */
    private $syncStorage;

    /**
     * @var SyncExecuteSourceEventListener
     */
    private $listener;

    /**
     * Test SyncExecuteItem ChunkSize getter and setter.
     */
    public function testChunkSizeGetterSetter()
    {
        $data = 1;
        $this->listener->setChunkSize($data);
        $result = $this->listener->getChunkSize();
        $this->assertEquals($data, $result);
    }

    /**
     * Test SyncExecuteItem ShopId getter and setter.
     */
    public function testShopIdGetterSetter()
    {
        $data = 1;
        $this->listener->setShopId($data);
        $result = $this->listener->getShopId();
        $this->assertEquals($data, $result);
    }

    /**
     * Test SyncExecuteItem DocumentType getter and setter.
     */
    public function testDocumentTypeGetterSetter()
    {
        $data = 'product';
        $this->listener->setDocumentType($data);
        $result = $this->listener->getDocumentType();
        $this->assertEquals($data, $result);
    }

    /**
     * Prepares variables for test.
     */
    protected function setUp()
    {
        $this->manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->elasticsearchManager = $this->getMockBuilder('ONGR\ElasticsearchBundle\ORM\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->syncStorage = $this->getMockBuilder('ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorage')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new SyncExecuteSourceEventListener(
            $this->manager,
            'p',
            $this->elasticsearchManager,
            'p',
            $this->syncStorage
        );
    }
}
