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

use ONGR\ConnectionsBundle\Sync\Extractor\Descriptor\JoinRelation;

class JoinRelationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests setter and getter.
     */
    public function testSetterAndGetter()
    {
        /** @var JoinRelation|\PHPUnit_Framework_MockObject_MockObject $relation */
        $relation = new JoinRelation('table', null, '1', 'type');

        $this->assertNull($relation->getDocumentId());
        $relation->setDocumentId('documentId');
        $this->assertEquals('documentId', $relation->getDocumentId());
    }
}
