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

use Doctrine\ORM\EntityManager;
use ONGR\ConnectionsBundle\Log\EventLoggerAwareTrait;
use ONGR\ConnectionsBundle\Pipeline\Event\SourcePipelineEvent;
use ONGR\ElasticsearchBundle\ORM\Manager;
use Psr\Log\LoggerAwareInterface;

/**
 * Class AbstractImportSourceEventListener - gets items from provider, creates empty Elasticsearch documents.
 */
abstract class AbstractImportSourceEventListener implements LoggerAwareInterface
{
    use EventLoggerAwareTrait;

    /**
     * @var EntityManager
     */
    private $doctrineManager;

    /**
     * @var string Type of source.
     */
    private $entityClass;

    /**
     * @var Manager
     */
    private $elasticsearchManager;

    /**
     * @var string Class name of Elasticsearch document. (e.g. AcmeTestBundle:Product).
     */
    private $documentClass;

    /**
     * @param EntityManager $manager
     * @param string        $entityClass
     * @param Manager       $elasticsearchManager
     * @param string        $documentClass
     */
    public function __construct(
        EntityManager $manager = null,
        $entityClass = null,
        Manager $elasticsearchManager = null,
        $documentClass = null
    ) {
        $this->doctrineManager = $manager;
        $this->entityClass = $entityClass;
        $this->elasticsearchManager = $elasticsearchManager;
        $this->documentClass = $documentClass;
    }

    /**
     * Gets data and adds source.
     *
     * @param SourcePipelineEvent $event
     */
    abstract public function onSource(SourcePipelineEvent $event);

    /**
     * @return EntityManager
     */
    public function getDoctrineManager()
    {
        if ($this->doctrineManager === null) {
            throw new \LogicException('Doctrine manager must be set before using \'getDoctrineManager\'');
        }

        return $this->doctrineManager;
    }

    /**
     * @param EntityManager $doctrineManager
     *
     * @return $this
     */
    public function setDoctrineManager($doctrineManager)
    {
        $this->doctrineManager = $doctrineManager;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        if ($this->entityClass === null) {
            throw new \LogicException('Entity class must be set before using \'getEntityClass\'');
        }

        return $this->entityClass;
    }

    /**
     * @param string $entityClass
     *
     * @return $this
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * @return Manager
     */
    public function getElasticsearchManager()
    {
        if ($this->elasticsearchManager === null) {
            throw new \LogicException('Elasticsearch manager must be set before using \'getElasticsearchManager\'');
        }

        return $this->elasticsearchManager;
    }

    /**
     * @param Manager $elasticsearchManager
     *
     * @return $this
     */
    public function setElasticsearchManager($elasticsearchManager)
    {
        $this->elasticsearchManager = $elasticsearchManager;

        return $this;
    }

    /**
     * @return string
     */
    public function getDocumentClass()
    {
        if ($this->documentClass === null) {
            throw new \LogicException('Document class must be set before using \'getDocumentClass\'');
        }

        return $this->documentClass;
    }

    /**
     * @param string $documentClass
     *
     * @return $this
     */
    public function setDocumentClass($documentClass)
    {
        $this->documentClass = $documentClass;

        return $this;
    }
}
