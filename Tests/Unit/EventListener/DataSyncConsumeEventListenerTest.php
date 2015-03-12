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

use ONGR\ConnectionsBundle\EventListener\DataSyncConsumeEventListener;
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\Sync\Extractor\ExtractorInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class DataSyncConsumeEventListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ItemPipelineEvent|MockObject
     */
    private $itemPipelineEvent;

    /**
     * @var ExtractorInterface|MockObject
     */
    private $extractor;

    /**
     * @var DataSyncConsumeEventListener
     */
    private $listener;

    /**
     * Setup services for tests.
     */
    protected function setUp()
    {
        $this->itemPipelineEvent = $this->getMockBuilder('ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extractor = $this->getMock('ONGR\ConnectionsBundle\Sync\Extractor\ExtractorInterface');
        $this->listener = new DataSyncConsumeEventListener($this->extractor);
    }

    /**
     * Test onConsume action.
     */
    public function testOnConsume()
    {
        $item = $this->getMock('ONGR\ConnectionsBundle\Sync\DiffProvider\Item\AbstractDiffItem');

        $this->itemPipelineEvent->expects($this->once())
            ->method('getItem')
            ->will($this->returnValue($item));

        $this->extractor->expects($this->once())
            ->method('extract')
            ->with($item);

        $this->listener->onConsume($this->itemPipelineEvent);
    }
}
