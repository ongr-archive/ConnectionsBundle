<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Pipeline;

use ONGR\ConnectionsBundle\Pipeline\PipelineFactory;
use ONGR\ConnectionsBundle\Pipeline\PipelineInterface;
use ONGR\ConnectionsBundle\Pipeline\PipelineStarter;

class PipelineStarterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests PipelineStarter.
     */
    public function testPipelineStarter()
    {
        /** @var PipelineInterface|\PHPUnit_Framework_MockObject_MockObject $pipeline */
        $pipeline = $this->getMockForAbstractClass('ONGR\ConnectionsBundle\Pipeline\PipelineInterface');
        $pipeline->expects($this->once())->method('start');

        /** @var PipelineFactory|\PHPUnit_Framework_MockObject_MockObject $factory */
        $factory = $this->getMock('ONGR\ConnectionsBundle\Pipeline\PipelineFactory');
        $factory->expects($this->once())->method('create')->with('prefix_target')->willReturn($pipeline);

        $starter = new PipelineStarter();
        $starter->setPipelineFactory($factory);

        $starter->startPipeline('prefix_', 'target');
    }
}
