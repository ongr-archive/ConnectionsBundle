<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Pipeline;

use ONGR\ConnectionsBundle\Pipeline\PipelineFactory;

/**
 * AbstractPipelineExecuteService class - creates pipeline and executes it.
 */
abstract class AbstractPipelineExecuteService
{
    /**
     * @var PipelineFactory
     */
    private $pipelineFactory;

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

    /**
     * Prepares pipeline name and executes pipeline.
     *
     * @param string $prefix
     * @param string $target
     */
    protected function executePipeline($prefix, $target)
    {
        if ($target === null) {
            $target = 'default';
        }

        $this->getPipelineFactory()->create($prefix . $target)->start();
    }
}
