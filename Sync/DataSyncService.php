<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync;

use ONGR\ConnectionsBundle\Pipeline\PipelineFactory;

/**
 * Data synchronization service.
 */
class DataSyncService
{
    /**
     * @var PipelineFactory
     */
    private $pipelineFactory;

    /**
     * Executes pipeline.
     *
     * @param string $pipelineName
     */
    public function startPipeline($pipelineName = 'default')
    {
        $pipeline = $this->getPipelineFactory()->create('data_sync.' . $pipelineName);

        $pipeline->start();
    }

    /**
     * @return PipelineFactory
     */
    public function getPipelineFactory()
    {
        return $this->pipelineFactory;
    }

    /**
     * @param PipelineFactory $pipelineFactory
     *
     * @return $this
     */
    public function setPipelineFactory($pipelineFactory)
    {
        $this->pipelineFactory = $pipelineFactory;

        return $this;
    }
}
