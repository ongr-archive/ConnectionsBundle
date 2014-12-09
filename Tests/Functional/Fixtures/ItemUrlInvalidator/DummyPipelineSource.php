<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\Fixtures\ItemUrlInvalidator;

use ONGR\ConnectionsBundle\Pipeline\Event\SourcePipelineEvent;

/**
 * Dummy pipeline source class for item invalidator tests.
 */
class DummyPipelineSource
{
    /**
     * Gives data for test.
     *
     * @param SourcePipelineEvent $event
     */
    public function onSource(SourcePipelineEvent $event)
    {
        $event->addSource(json_decode(file_get_contents(__DIR__ . '/dummyData.json')));
    }
}
