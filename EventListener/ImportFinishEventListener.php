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
     * @var Manager
     */
    private $elasticsearchManager;

    /**
     * @param Manager $elasticsearchManager
     */
    public function __construct(Manager $elasticsearchManager = null)
    {
        $this->elasticsearchManager = $elasticsearchManager;
    }

    /**
     * Finish and commit.
     */
    public function onFinish()
    {
        $this->getElasticsearchManager()->commit();
    }

    /**
     * @return Manager
     */
    public function getElasticsearchManager()
    {
        if ($this->elasticsearchManager === null) {
            throw new \LogicException('Manager must be set before using \'getProvider\'');
        }

        return $this->elasticsearchManager;
    }

    /**
     * @param Manager $elasticsearchManager
     *
     * @return $this
     */
    public function setElasticsearchManager(Manager $elasticsearchManager)
    {
        $this->elasticsearchManager = $elasticsearchManager;

        return $this;
    }
}
