<?php

namespace ONGR\ConnectionsBundle\EventListener;

use Doctrine\ORM\EntityManager;
use ONGR\ConnectionsBundle\Pipeline\Event\SourcePipelineEvent;
use ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorageInterface;
use ONGR\ConnectionsBundle\Sync\SyncStorageImportIterator;
use ONGR\ElasticsearchBundle\ORM\Manager;

/**
 * Class SyncExecuteSourceEventListener - creates iterator which provides modified entities.
 */
class SyncExecuteSourceEventListener extends AbstractImportSourceEventListener
{
    /**
     * @var SyncStorageInterface
     */
    private $syncStorage;

    /**
     * @var int
     */
    private $shopId = 1;

    /**
     * @var int
     */
    private $chunkSize = 1;

    /**
     * @var string
     */
    private $documentType = '';

    /**
     * @param EntityManager        $manager
     * @param string               $entityClass
     * @param Manager              $elasticsearchManager
     * @param string               $documentClass
     * @param SyncStorageInterface $syncStorage
     */
    public function __construct(
        EntityManager $manager = null,
        $entityClass = null,
        Manager $elasticsearchManager = null,
        $documentClass = null,
        $syncStorage = null
    ) {
        parent::__construct($manager, $entityClass, $elasticsearchManager, $documentClass);
        $this->syncStorage = $syncStorage;
    }

    /**
     * Gets iterator for all documents which need to be updated.
     *
     * @return SyncStorageImportIterator
     */
    public function getDocuments()
    {
        return new SyncStorageImportIterator(
            [
                'sync_storage' => $this->getSyncStorage(),
                'shop_id' => $this->getShopId(),
                'document_type' => $this->getDocumentType(),
            ],
            $this->getElasticsearchManager()->getRepository($this->getDocumentClass()),
            $this->getDoctrineManager(),
            $this->getEntityClass()
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

    /**
     * @return int
     */
    public function getChunkSize()
    {
        return $this->chunkSize;
    }

    /**
     * @param int $chunkSize
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
     */
    public function setDocumentType($documentType)
    {
        $this->documentType = $documentType;
    }

    /**
     * @return SyncStorageInterface
     */
    public function getSyncStorage()
    {
        if ($this->syncStorage === null) {
            throw new \LogicException('Sync storage must be set before using \'getSyncStorage\'');
        }

        return $this->syncStorage;
    }

    /**
     * @param SyncStorageInterface $syncStorage
     *
     * @return $this
     */
    public function setSyncStorage($syncStorage)
    {
        $this->syncStorage = $syncStorage;

        return $this;
    }
}
