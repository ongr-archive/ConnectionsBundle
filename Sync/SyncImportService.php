<?php

namespace ONGR\ConnectionsBundle\Sync;

use ONGR\ConnectionsBundle\Pipeline\PipelineExecuterTrait;
use ONGR\ConnectionsBundle\Pipeline\PipelineFactory;

/**
 * SyncImportService class.
 */
class SyncImportService
{
    use PipelineExecuterTrait;

    /**
     * @var PipelineFactory
     */
    private $pipelineFactory;

    /**
     * Runs import from panther process.
     *
     * @param string $target
     *
     * @return void
     */
    public function import($target = null)
    {
        $this->executePipeline('sync.import.', $target);
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
     * @return void
     */
    public function setPipelineFactory($pipelineFactory)
    {
        $this->pipelineFactory = $pipelineFactory;
    }
}
