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

use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Pipeline factory.
 */
class PipelineFactory
{
    /**
     * @var string This consumer simply returns it's item to pipeline.
     */
    const CONSUMER_RETURN = '__consumer_return';

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var string
     */
    private $className;

    /**
     * @var ProgressBar
     */
    private $progressBar = null;

    /**
     * Creates pipeline and registers first listeners in dispatcher.
     *
     * Available listeners:
     * - $listeners['sources']: pass source listeners.
     * - $listeners['modifiers']: pass modifier listeners.
     * - $listeners['consumers']: pass consumer listeners.
     *
     * @param string $pipelineName
     * @param array  $listeners
     *
     * @return \ONGR\ConnectionsBundle\Pipeline\PipelineInterface
     * @throws \InvalidArgumentException
     */
    public function create($pipelineName, $listeners = [])
    {
        $listeners = array_merge(
            [
                'sources' => [],
                'modifiers' => [],
                'consumers' => [],
            ],
            $listeners
        );

        $className = $this->getClassName();
        /** @var PipelineInterface $pipeline */
        $pipeline = new $className($pipelineName);

        if (!$pipeline instanceof Pipeline) {
            throw new \InvalidArgumentException('Pipeline class\' name must implement PipelineInterface');
        }

        $pipeline->setProgressBar($this->getProgressBar());

        $dispatcher = $this->getDispatcher();
        $pipeline->setDispatcher($dispatcher);

        foreach ($listeners['consumers'] as &$listener) {
            if ($listener === self::CONSUMER_RETURN) {
                $listener = function (ItemPipelineEvent $event) {
                    $event->setOutput($event->getItem());
                };
            }
        }

        $registerListener = function ($key, $suffix) use ($listeners, $dispatcher, $pipeline) {
            foreach ($listeners[$key] as $listener) {
                $dispatcher->addListener(
                    $pipeline->getEventName($suffix),
                    $listener
                );
            }
        };
        $registerListener('sources', Pipeline::EVENT_SUFFIX_SOURCE);
        $registerListener('modifiers', Pipeline::EVENT_SUFFIX_MODIFY);
        $registerListener('consumers', Pipeline::EVENT_SUFFIX_CONSUME);

        return $pipeline;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function setDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return ProgressBar
     */
    public function getProgressBar()
    {
        return $this->progressBar;
    }

    /**
     * @param ProgressBar $progress
     */
    public function setProgressBar($progress)
    {
        $this->progressBar = $progress;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }
}
