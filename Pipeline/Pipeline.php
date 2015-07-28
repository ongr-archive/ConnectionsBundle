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

use ONGR\ConnectionsBundle\Pipeline\Event\ContextAwareTrait;
use ONGR\ConnectionsBundle\Pipeline\Event\FinishPipelineEvent;
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\Pipeline\Event\SourcePipelineEvent;
use ONGR\ConnectionsBundle\Pipeline\Event\StartPipelineEvent;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Basic pipeline implementation using dispatcher.
 */
class Pipeline implements PipelineInterface
{
    use ContextAwareTrait;

    const EVENT_SUFFIX_SOURCE = 'source';
    const EVENT_SUFFIX_MODIFY = 'modify';
    const EVENT_SUFFIX_CONSUME = 'consume';
    const EVENT_SUFFIX_FINISH = 'finish';
    const EVENT_SUFFIX_START = 'start';

    /**
     * @var string Pipeline name used in event naming.
     */
    private $name;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var ProgressBar
     */
    private $progressBar = null;

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        $dispatcher = $this->getDispatcher();
        $sourceEvent = new SourcePipelineEvent();
        $sourceEvent->setContext($this->getContext());
        $dispatcher->dispatch(
            $this->getEventName(self::EVENT_SUFFIX_SOURCE),
            $sourceEvent
        );
        $this->setContext($sourceEvent->getContext());
        $sources = $sourceEvent->getSources();
        $outputs = [];

        $startEvent = new StartPipelineEvent();
        $startEvent->setContext($this->getContext());
        $startEvent->setItemCount($this->countSourceItems($sources));

        $dispatcher->dispatch(
            $this->getEventName(self::EVENT_SUFFIX_START),
            $startEvent
        );
        $this->setContext($startEvent->getContext());

        $count = $this->countSourceItems($sources);

        $this->progressBar && $this->progressBar->setRedrawFrequency($count > 10 ? $count / 10 : 1);
        $this->progressBar && $this->progressBar->start($count);

        foreach ($sources as $source) {
            foreach ($source as $item) {
                $itemEvent = new ItemPipelineEvent($item);
                $itemEvent->setContext($this->getContext());

                $dispatcher->dispatch(
                    $this->getEventName(self::EVENT_SUFFIX_MODIFY),
                    $itemEvent
                );

                $dispatcher->dispatch(
                    $this->getEventName(self::EVENT_SUFFIX_CONSUME),
                    $itemEvent
                );

                $output = $itemEvent->getOutput();
                if ($output !== null) {
                    $outputs[] = $output;
                }

                $this->setContext($itemEvent->getContext());

                $this->progressBar && $this->progressBar->advance();
            }
        }

        $finishEvent = new FinishPipelineEvent();
        $finishEvent->setContext($this->getContext());
        $dispatcher->dispatch(
            $this->getEventName(self::EVENT_SUFFIX_FINISH),
            $finishEvent
        );

        $this->progressBar && $this->progressBar->finish();

        return ['outputs' => $outputs];
    }

    /**
     * {@inheritdoc}
     */
    public static function generateEventName($pipelineName, $suffix)
    {
        return "ongr.pipeline.{$pipelineName}.{$suffix}";
    }

    /**
     * {@inheritdoc}
     */
    public function getEventName($suffix)
    {
        return static::generateEventName($this->getName(), $suffix);
    }

    /**
     * {@inheritdoc}
     *
     * @see Pipeline::$name
     */
    public function __construct($name)
    {
        $this->setName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getProgressBar()
    {
        return $this->progressBar;
    }

    /**
     * {@inheritdoc}
     */
    public function setProgressBar($progress)
    {
        $this->progressBar = $progress;
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Counts number of items in the services.
     *
     * @param \Traversable[]|array[] $sources
     *
     * @return int
     */
    private function countSourceItems($sources)
    {
        $count = 0;
        foreach ($sources as $source) {
            $count += count($source);
        }

        return $count;
    }
}
