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

use ONGR\ConnectionsBundle\Sync\Extractor\Descriptor\AbstractRelation;

class AbstractRelationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests setter and getter.
     */
    public function testSetterAndGetter()
    {
        /** @var AbstractRelation|\PHPUnit_Framework_MockObject_MockObject $relation */
        $relation = $this->getMockForAbstractClass(
            'ONGR\ConnectionsBundle\Sync\Extractor\Descriptor\AbstractRelation',
            [null]
        );

        $this->assertNull($relation->getDocumentType());
        $relation->setDocumentType('testType');
        $this->assertEquals('testType', $relation->getDocumentType());
    }
}
