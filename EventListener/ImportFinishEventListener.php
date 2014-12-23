<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\EventListener;

use ONGR\ElasticsearchBundle\ORM\Manager;

/**
 * ImportFinishEventListener - commits document to elasticsearch.
 */
class ImportFinishEventListener
{
    /**
     * @var Manager Manager.
     */
    protected $manager;

    /**
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Finish and commit.
     */
    public function onFinish()
    {
        $this->manager->commit();
    }
}
