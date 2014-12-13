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
    protected $syncStorage;

    /**
     * @var int
     */
    protected $shopId = 1;

    /**
     * @var int
     */
    protected $chunkSize = 1;

    /**
     * @var string
     */
    protected $documentType = '';

    /**
     * @param EntityManager        $manager
     * @param string               $entityClass
     * @param Manager              $elasticsearchManager
     * @param string               $documentClass
     * @param SyncStorageInterface $syncStorage
     */
    public function __construct(
        EntityManager $manager,
        $entityClass,
        Manager $elasticsearchManager,
        $documentClass,
        $syncStorage
    ) {
        parent::__construct($manager, $entityClass, $elasticsearchManager, $documentClass);
        $this->syncStorage = $syncStorage;
    }

    /**
     * Gets iterator for all which need to be updated.
     *
     * @return SyncStorageImportIterator
     */
    public function getDocuments()
    {
        return new SyncStorageImportIterator(
            [
                'sync_storage' => $this->syncStorage,
                'shop_id' => $this->shopId,
                'document_type' => $this->documentType,
            ],
            $this->elasticsearchManager->getRepository($this->documentClass),
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
}
