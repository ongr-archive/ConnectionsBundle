<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Unit\Sync\Extractor\Descriptor;

use ONGR\ConnectionsBundle\Sync\Extractor\Descriptor\ExtractionCollection;
use ONGR\ConnectionsBundle\Sync\Extractor\Descriptor\ExtractorDescriptor;

/**
 * ExtractionCollectionTest class.
 */
class ExtractionCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests setter and getter.
     */
    public function testSetterAndGetter()
    {
        $collection = new ExtractionCollection();
        $this->assertEquals([], $collection->getDescriptors());

        /** @var ExtractorDescriptor|\PHPUnit_Framework_MockObject_MockObject $descriptor1 */
        $descriptor1 = $this->getMock(
            'ONGR\ConnectionsBundle\Sync\Extractor\Descriptor\ExtractorDescriptor',
            [],
            [],
            '',
            false
        );
        /** @var ExtractorDescriptor|\PHPUnit_Framework_MockObject_MockObject $descriptor2 */
        $descriptor2 = $this->getMock(
            'ONGR\ConnectionsBundle\Sync\Extractor\Descriptor\ExtractorDescriptor',
            [],
            [],
            '',
            false
        );

        $collection->setDescriptors([$descriptor1]);
        $this->assertSame([$descriptor1], $collection->getDescriptors());

        $collection->addDescriptor($descriptor2);
        $this->assertSame([$descriptor1, $descriptor2], $collection->getDescriptors());
    }
}
