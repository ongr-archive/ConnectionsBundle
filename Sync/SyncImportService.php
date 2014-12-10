<?php

namespace ONGR\ConnectionsBundle\Sync;

use ONGR\ConnectionsBundle\Pipeline\PipelineFactory;

/**
 * SyncImportService class.
 */
class SyncImportService
{
    /**
     * @var PipelineFactory
     */
    private $pipelineFactory;

    /**
     * Runs import from panther process.
     *
     * @param string $target
     */
    public function import($target = null)
    {
        if ($target === null) {
            $target = 'default';
        }
        $pipeline = $this->getPipelineFactory()->create(
            "sync.import.$target"
        );

        $pipeline->execute();
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
     */
    public function setPipelineFactory($pipelineFactory)
    {
        $this->pipelineFactory = $pipelineFactory;
    }
}
