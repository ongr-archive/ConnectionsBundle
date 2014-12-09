<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Pipeline\Event;

use ONGR\ConnectionsBundle\Pipeline\Event\SourcePipelineEvent;

/**
 * Test for SourcePipelineEvent.
 */
class SourcePipelineEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test setSources method.
     */
    public function testSetSources()
    {
        $event = new SourcePipelineEvent();
        $event->setSources([['foo']]);

        $this->assertEquals([['foo']], $event->getSources());
    }

    /**
     * Test addSource with non \Traversable.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage source must be of type \Traversable|array
     */
    public function testAddSourceNotTraversable()
    {
        $event = new SourcePipelineEvent();
        $event->addSource('foo');
    }
}
