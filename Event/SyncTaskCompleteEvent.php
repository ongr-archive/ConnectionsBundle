<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Synchronise task event class.
 */
class SyncTaskCompleteEvent extends Event
{
    const EVENT_NAME = 'ongr_connections.sync_task_complete';

    // Task types.
    const TASK_TYPE_DOWNLOAD = 'download';
    const TASK_TYPE_CONVERT = 'convert';
    const TASK_TYPE_PUSH = 'push';

    // Possible data types.
    const DATA_TYPE_FULL_DOCUMENTS = 'full_documents';
    const DATA_TYPE_PARTIAL_DOCUMENTS = 'partial_documents';

    /**
     * @var string
     */
    protected $taskType;

    /**
     * @var string
     */
    protected $inputFile;

    /**
     * @var string
     */
    protected $outputFile;

    /**
     * @var string
     */
    protected $provider;

    /**
     * @var string
     */
    protected $dataType = self::DATA_TYPE_FULL_DOCUMENTS;

    /**
     * @var string Optional description for data. For example "delta", "prices", ...
     */
    protected $dataDescription;

    /**
     * @return string
     */
    public function getInputFile()
    {
        return $this->inputFile;
    }

    /**
     * @param string $inputFile
     */
    public function setInputFile($inputFile)
    {
        $this->inputFile = $inputFile;
    }

    /**
     * @return string
     */
    public function getOutputFile()
    {
        return $this->outputFile;
    }

    /**
     * @param string $outputFile
     */
    public function setOutputFile($outputFile)
    {
        $this->outputFile = $outputFile;
    }

    /**
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param string $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return string
     */
    public function getTaskType()
    {
        return $this->taskType;
    }

    /**
     * @param string $taskType
     */
    public function setTaskType($taskType)
    {
        $this->taskType = $taskType;
    }

    /**
     * @return string
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param string $dataType
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;
    }

    /**
     * @return string
     */
    public function getDataDescription()
    {
        return $this->dataDescription;
    }

    /**
     * @param string $dataDescription
     */
    public function setDataDescription($dataDescription)
    {
        $this->dataDescription = $dataDescription;
    }
}
