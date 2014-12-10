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

use Doctrine\ORM\EntityManager;
use ONGR\ConnectionsBundle\Event\SyncImportItem;
use ONGR\ConnectionsBundle\Sync\Panther\Panther;
use ONGR\ConnectionsBundle\Sync\Panther\PantherInterface;
use ONGR\ElasticsearchBundle\ORM\Repository;

/**
 * This class is able to iterate over entities without storing objects in memory.
 */
class PantherImportIterator implements \Iterator
{
    /**
     * @var Panther
     */
    private $panther;

    /**
     * @var Repository $repository Elasticsearch repository.
     */
    private $repository;

    /**
     * @var mixed
     */
    private $currentEntity;

    /**
     * @var array
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
     * @var EntityManager
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
     * @param array         $pantherParams
     * @param Repository    $repository
     * @param EntityManager $entityManager
     * @param string        $entityClass
     */
    public function __construct($pantherParams, Repository $repository, EntityManager $entityManager, $entityClass)
    {
        $this->panther = $pantherParams['panther'];
        $this->shopId = $pantherParams['shop_id'];
        $this->documentType = $pantherParams['document_type'];
        $this->repository = $repository;
        $this->entityManager = $entityManager;
        $this->entityClass = $entityClass;

        $this->getNewChunk();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        // Do nothing.
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return new SyncImportItem(
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
        $this->getNewChunk();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        if ($this->valid()) {
            return key($this->currentChunk[0]['document_id']);
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

    /**
     * Gets chunk and sets valid and entity.
     */
    private function getNewChunk()
    {
        $this->currentChunk = $this->panther->getChunk(1, $this->documentType, $this->shopId);

        if (empty($this->currentChunk)) {
            $this->valid = false;
        } else {
            $this->valid = true;

            $this->currentEntity = $this
                ->entityManager
                ->getRepository($this->entityClass)->find($this->currentChunk[0]['document_id']);
            if (!empty($this->currentEntity)) {
                $this->valid = true;
            } elseif ($this->currentChunk[0]['type'] == PantherInterface::OPERATION_DELETE) {
                $this->currentEntity = null;
                $this->valid = true;
            } else {
                $this->valid = false;
            }
        }
    }
}
