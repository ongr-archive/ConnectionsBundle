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

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Responsible for running full pipeline with data import and event firing.
 */
interface PipelineInterface
{
    /**
     * @param string $pipelineName
     */
    public function __construct($pipelineName);

    /**
     * Execute pipeline.
     */
    public function start();

    /**
     * @return EventDispatcherInterface
     */
    public function getDispatcher();

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function setDispatcher($dispatcher);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return mixed
     */
    public function getContext();

    /**
     * @param mixed $context
     */
    public function setContext($context);

    /**
     * Construct and return event name.
     *
     * @param string $pipelineName
     * @param string $suffix
     *
     * @return string
     */
    public static function generateEventName($pipelineName, $suffix);

    /**
     * Construct and return event name.
     *
     * @param string $suffix
     *
     * @return string
     */
    public function getEventName($suffix);
}
