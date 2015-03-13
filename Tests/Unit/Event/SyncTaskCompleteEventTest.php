<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Event;

use ONGR\ConnectionsBundle\Tests\Unit\Entity\AbstractEntityTest;

/**
 * Tests SyncTaskCompleteEventTest setters and getters.
 */
class SyncTaskCompleteEventTest extends AbstractEntityTest
{
    /**
     * {@inheritdoc}
     */
    public function getFieldsData()
    {
        return [
            ['taskType'],
            ['inputFile'],
            ['outputFile'],
            ['provider'],
            ['dataType'],
            ['dataDescription'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        return 'ONGR\ConnectionsBundle\Event\SyncTaskCompleteEvent';
    }
}
