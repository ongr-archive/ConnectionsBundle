<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\UrlInvalidator;

use ONGR\ConnectionsBundle\Pipeline\Event\FinishPipelineEvent;
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\UrlInvalidator\AbstractItemUrlInvalidator;
use ONGR\ConnectionsBundle\UrlInvalidator\UrlInvalidatorService;
use ONGR\ElasticsearchBundle\ORM\Manager;
use Symfony\Component\Routing\RouterInterface;

class ItemUrlInvalidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests AbstractItemUrlInvalidator.
     */
    public function testItemUrlInvalidator() {

        /** @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject $router */
        $router = $this->getMockForAbstractClass('Symfony\Component\Routing\RouterInterface');

        /** @var Manager|\PHPUnit_Framework_MockObject_MockObject $manager */
        $manager = $this->getMockForAbstractClass(
            'ONGR\ElasticsearchBundle\ORM\Manager',
            [null, null]
        );

        /** @var UrlInvalidatorService|\PHPUnit_Framework_MockObject_MockObject $invalidatorService */
        $invalidatorService = $this->getMock(
            'ONGR\ConnectionsBundle\UrlInvalidator\UrlInvalidatorService',
            [],
            [$router, $manager, null, null, null]
        );

        /** @var AbstractItemUrlInvalidator|\PHPUnit_Framework_MockObject_MockObject $invalidator */
        $invalidator = $this->getMockForAbstractClass(
            'ONGR\ConnectionsBundle\UrlInvalidator\AbstractItemUrlInvalidator',
            [],
            '',
            null
        );

        $invalidator->setUrlInvalidator($invalidatorService);
        $this->assertSame($invalidatorService, $invalidator->getUrlInvalidator());

        /** @var ItemPipelineEvent|\PHPUnit_Framework_MockObject_MockObject $itemEvent */
        $itemEvent = $this->getMock('ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent', [], [null]);
        $itemEvent->expects($this->any())->method('getItem')->willReturn('Item');
        $itemEvent->expects($this->any())->method('getContext')->willReturn('Context');

        $invalidator->expects($this->any())->method('invalidateItem')->with('Item', 'Context');

        $invalidator->consume($itemEvent);

        /** @var FinishPipelineEvent|\PHPUnit_Framework_MockObject_MockObject $finishEvent */
        $finishEvent = $this->getMock('ONGR\ConnectionsBundle\Pipeline\Event\FinishPipelineEvent');

        $invalidatorService->expects($this->once())->method('invalidate');

        $invalidator->onFinish($finishEvent);
    }
}
