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
    protected $shopId = 1;

    /**
     * @var int
     */
    protected $chunkSize = 1;

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
     * @var string Class name of Elasticsearch document. (e.g. Product)
     */
    protected $documentClass;

    /**
     * @var string
     */
    protected $documentType = '';

    /**
     * @param EntityManager    $manager
     * @param string           $entityClass
     * @param Manager          $elasticSearchManager
     * @param string           $documentClass
     * @param PantherInterface $panther
     */
    public function __construct(
        EntityManager $manager,
        $entityClass,
        Manager $elasticSearchManager,
        $documentClass,
        $panther
    ) {
        $this->entityManager = $manager;
        $this->entityClass = $entityClass;
        $this->elasticSearchManager = $elasticSearchManager;
        $this->documentClass = $documentClass;
        $this->panther = $panther;
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
     *
     * @return void
     */
    public function onSource(SourcePipelineEvent $event)
    {
        $event->addSource($this->getDocuments());
    }

    /**
     * @return int
     */
    public function getChunkSize()
    {
        return $this->chunkSize;
    }

    /**
     * @param int $chunkSize
     *
     * @return void
     */
    public function setChunkSize($chunkSize)
    {
        $this->chunkSize = $chunkSize;
    }

    /**
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @param int $shopId
     *
     * @return void
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;
    }

    /**
     * @return string
     */
    public function getDocumentType()
    {
        return $this->documentType;
    }

    /**
     * @param string $documentType
     *
     * @return void
     */
    public function setDocumentType($documentType)
    {
        $this->documentType = $documentType;
    }
}
