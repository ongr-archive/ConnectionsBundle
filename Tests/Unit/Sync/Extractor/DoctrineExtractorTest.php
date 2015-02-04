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
use ONGR\ConnectionsBundle\Sync\Extractor\DoctrineExtractor;
use ONGR\ConnectionsBundle\Sync\Extractor\Relation\RelationsCollection;
use ONGR\ConnectionsBundle\Sync\Extractor\Relation\SqlRelation;
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

        /** @var SqlRelation|\PHPUnit_Framework_MockObject_MockObject $relationsCollection $relation */
        $relation = $this->getMock('ONGR\ConnectionsBundle\Sync\Extractor\Relation\SqlRelation');

        $relation->expects($this->any())->method('getTriggerTypeAlias')->willReturn(ActionTypes::UPDATE);
        $relation->expects($this->any())->method('getTable')->willReturn('table');

        /** @var RelationsCollection|\PHPUnit_Framework_MockObject_MockObject $relationsCollection */
        $relationsCollection = $this->getMock('ONGR\ConnectionsBundle\Sync\Extractor\Relation\RelationsCollection');
        $relationsCollection->expects($this->any())->method('getRelations')->willReturn([$relation]);

        $extractor = new DoctrineExtractor();
        $extractor->setConnection($connection);
        $extractor->setRelationsCollection($relationsCollection);

        /** @var UpdateDiffItem|\PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->getMock('ONGR\ConnectionsBundle\Sync\DiffProvider\Item\UpdateDiffItem');
        $item->expects($this->any())->method('getCategory')->willReturn('table');

        $this->setExpectedException('\LogicException', 'Relation does not have any effect');
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

        /** @var SqlRelation|\PHPUnit_Framework_MockObject_MockObject $relationsCollection $relation */
        $relation = $this->getMock('ONGR\ConnectionsBundle\Sync\Extractor\Relation\SqlRelation');

        $this->setExpectedException(
            '\InvalidArgumentException',
            'Wrong diff item type. Got: ONGR\ConnectionsBundle\Sync\DiffProvider\Item\CreateDiffItem'
        );

        $method->invoke(new DoctrineExtractor(), new CreateDiffItem(), $relation);
    }
}
