<?php

namespace ONGR\ConnectionsBundle\Event;

use Doctrine\ORM\EntityManager;
use ONGR\ConnectionsBundle\Pipeline\Event\SourcePipelineEvent;
use ONGR\ConnectionsBundle\Sync\Panther\Panther;
use ONGR\ConnectionsBundle\Sync\Panther\PantherInterface;
use ONGR\ConnectionsBundle\Sync\PantherImportIterator;
use ONGR\ElasticsearchBundle\ORM\Manager;

/**
 * Class ImportSourceEvent.
 */
class SyncImportSourceEvent
{
    /**
     * @var Panther
     */
    protected $panther;

    /**
     * @var int
     */
    protected $shopId;

    /**
     * @var int
     */
    protected $chunkSize;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var string Type of source.
     */
    protected $entityClass;

    /**
     * @var Manager
     */
    protected $elasticSearchManager;

    /**
     * @var string Classname of Elasticsearch document. (e.g. Product)
     */
    protected $documentClass;

    /**
     * @param EntityManager    $manager
     * @param string           $entityClass
     * @param Manager          $elasticSearchManager
     * @param string           $documentClass
     * @param PantherInterface $panther
     * @param int              $shopId
     * @param int              $chunkSize
     * @param string           $documentType
     */
    public function __construct(
        EntityManager $manager,
        $entityClass,
        Manager $elasticSearchManager,
        $documentClass,
        $panther,
        $shopId,
        $chunkSize,
        $documentType
    ) {
        $this->entityManager = $manager;
        $this->entityClass = $entityClass;
        $this->elasticSearchManager = $elasticSearchManager;
        $this->documentClass = $documentClass;
        $this->panther = $panther;
        $this->shopId = $shopId;
        $this->documentType = $documentType;
        $this->chunkSize = $chunkSize;
    }

    /**
     * Gets iterator for all which need to be updated.
     *
     * @return PantherImportIterator
     */
    public function getDocuments()
    {
        return new PantherImportIterator(
            [
                'panther' => $this->panther,
                'shop_id' => $this->shopId,
                'document_type' => $this->documentType,
            ],
            $this->elasticSearchManager->getRepository($this->documentClass),
            $this->entityManager,
            $this->entityClass
        );
    }

    /**
     * Gets data and adds source.
     *
     * @param SourcePipelineEvent $event
     */
    public function onSource(SourcePipelineEvent $event)
    {
        $event->addSource($this->getDocuments());
    }
}
