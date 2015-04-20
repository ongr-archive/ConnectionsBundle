<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Sync\Extractor;

use Doctrine\DBAL\Connection;
use ONGR\ConnectionsBundle\Sync\ActionTypes;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\CreateDiffItem;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\UpdateDiffItem;
use ONGR\ConnectionsBundle\Sync\Extractor\Descriptor\ExtractionCollection;
use ONGR\ConnectionsBundle\Sync\Extractor\Descriptor\ExtractionDescriptor;
use ONGR\ConnectionsBundle\Sync\Extractor\DoctrineExtractor;
use ONGR\ConnectionsBundle\Tests\Unit\Fixtures\Sync\Extractor\InvalidDiffItem;
use ReflectionClass;

class DoctrineExtractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests invalid relation.
     */
    public function testInvalidRelation()
    {
        /** @var Connection|\PHPUnit_Framework_MockObject_MockObject $connection */
        $connection = $this->getMock('Doctrine\DBAL\Connection', [], [], '', false);

        /** @var ExtractionDescriptor|\PHPUnit_Framework_MockObject_MockObject $extractionCollection $descriptor */
        $descriptor = $this->getMock('ONGR\ConnectionsBundle\Sync\Extractor\Descriptor\ExtractionDescriptor');

        $descriptor->expects($this->any())->method('getTriggerTypeAlias')->willReturn(ActionTypes::UPDATE);
        $descriptor->expects($this->any())->method('getTable')->willReturn('table');
        $descriptor->expects($this->any())->method('getName')->willReturn('descriptor');

        /** @var ExtractionCollection|\PHPUnit_Framework_MockObject_MockObject $extractionCollection */
        $extractionCollection = $this->getMock('ONGR\ConnectionsBundle\Sync\Extractor\Descriptor\ExtractionCollection');
        $extractionCollection->expects($this->any())->method('getDescriptors')->willReturn([$descriptor]);

        $extractor = new DoctrineExtractor();
        $extractor->setConnection($connection);
        $extractor->setExtractionCollection($extractionCollection);

        /** @var UpdateDiffItem|\PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->getMock('ONGR\ConnectionsBundle\Sync\DiffProvider\Item\UpdateDiffItem');
        $item->expects($this->any())->method('getCategory')->willReturn('table');

        $this->setExpectedException(
            '\LogicException',
            'Missing relations or no document type set in descriptor "descriptor"'
        );
        $extractor->extract($item);
    }

    /**
     * Tests resolveItemAction exception.
     */
    public function testResolveItemActionException()
    {
        $class = new ReflectionClass('ONGR\ConnectionsBundle\Sync\Extractor\DoctrineExtractor');
        $method = $class->getMethod('resolveItemAction');
        $method->setAccessible(true);

        $this->setExpectedException(
            '\InvalidArgumentException',
            'Unsupported diff item type. Got: ONGR\ConnectionsBundle\Tests\Unit\Fixtures\Sync\Extractor\InvalidDiffItem'
        );
        $method->invoke(new DoctrineExtractor(), new InvalidDiffItem());
    }

    /**
     * Tests isTrackedFieldModified exception.
     */
    public function testIsTrackedFieldModified()
    {
        $class = new ReflectionClass('ONGR\ConnectionsBundle\Sync\Extractor\DoctrineExtractor');
        $method = $class->getMethod('isTrackedFieldModified');
        $method->setAccessible(true);

        /** @var ExtractionDescriptor|\PHPUnit_Framework_MockObject_MockObject $relationsCollection $descriptor */
        $descriptor = $this->getMock('ONGR\ConnectionsBundle\Sync\Extractor\Descriptor\ExtractionDescriptor');

        $this->setExpectedException(
            '\InvalidArgumentException',
            'Wrong diff item type. Got: ONGR\ConnectionsBundle\Sync\DiffProvider\Item\CreateDiffItem'
        );

        $method->invoke(new DoctrineExtractor(), new CreateDiffItem(), $descriptor);
    }

    /**
     * Check if exception is thrown when connection is not set.
     *
     * @expectedException \LogicException
     */
    public function testConnectionSetter()
    {
        $extractor = new DoctrineExtractor();
        $extractor->setConnection(null);
        $extractor->getConnection();
    }
}
