<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync;

use Doctrine\ORM\EntityManagerInterface;
use ONGR\ConnectionsBundle\Import\Item\SyncExecuteItem;
use ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorage;
use ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorageInterface;
use ONGR\ElasticsearchBundle\ORM\Repository;

/**
 * This class is able to iterate over entities without storing objects in memory.
 */
class SyncStorageImportIterator implements \Iterator
{
    /**
     * @var SyncStorage
     */
    private $syncStorage;

    /**
     * @var Repository Elasticsearch repository.
     */
    private $repository;

    /**
     * @var mixed
     */
    private $currentEntity;

    /**
     * @var array|null
     */
    private $currentChunk;

    /**
     * @var int
     */
    private $shopId;

    /**
     * @var string
     */
    private $documentType;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var bool
     */
    private $valid;

    /**
     * @param array                  $syncStorageParams
     * @param Repository             $repository
     * @param EntityManagerInterface $entityManager
     * @param string                 $entityClass
     */
    public function __construct(
        $syncStorageParams,
        Repository $repository,
        EntityManagerInterface $entityManager,
        $entityClass
    ) {
        $this->syncStorage = $syncStorageParams['sync_storage'];
        $this->shopId = $syncStorageParams['shop_id'];
        $this->documentType = $syncStorageParams['document_type'];
        $this->repository = $repository;
        $this->entityManager = $entityManager;
        $this->entityClass = $entityClass;
    }

    /**
     * This iterator cannot rewind. Method rewind() just initializes iterator before usage in foreach cycle.
     */
    public function rewind()
    {
        $this->next();
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return new SyncExecuteItem(
            $this->currentEntity,
            $this->repository->createDocument(),
            $this->currentChunk[0]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->currentChunk = $this->syncStorage->getChunk(1, $this->documentType, $this->shopId);

        if (empty($this->currentChunk)) {
            $this->valid = false;

            return;
        }

        $this->currentEntity = $this
            ->entityManager
            ->getRepository($this->entityClass)->find($this->currentChunk[0]['document_id']);

        if (!empty($this->currentEntity) || $this->currentChunk[0]['type'] == SyncStorageInterface::OPERATION_DELETE) {
            $this->valid = true;

            return;
        }

        $this->valid = false;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        if ($this->valid()) {
            return $this->currentChunk[0]['document_id'];
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->valid;
    }
}
