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

use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\Pipeline\Item\SyncExecuteItem;
use ONGR\ConnectionsBundle\Sync\ActionTypes;
use ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorageInterface;
use ONGR\ElasticsearchBundle\ORM\Manager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;

/**
 * SyncExecuteConsumeEventListener class, called after modify event.
 *
 * Puts/updates or deletes document into/from Elasticsearch.
 */
class SyncExecuteConsumeEventListener extends AbstractImportConsumeEventListener implements LoggerAwareInterface
{
    /**
     * @var string
     */
    private $documentClass;

    /**
     * @var SyncStorageInterface
     */
    private $syncStorage;

    /**
     * @var array
     */
    private $syncStorageData;

    /**
     * @param Manager              $elasticsearchManager
     * @param string               $documentClass
     * @param SyncStorageInterface $syncStorage
     */
    public function __construct(
        Manager $elasticsearchManager = null,
        $documentClass = null,
        SyncStorageInterface $syncStorage = null
    ) {
        parent::__construct($elasticsearchManager, 'ONGR\ConnectionsBundle\Pipeline\Item\SyncExecuteItem');
        $this->documentClass = $documentClass;
        $this->syncStorage = $syncStorage;
    }

    /**
     * @return string
     */
    public function getDocumentClass()
    {
        if ($this->documentClass === null) {
            throw new \LogicException('Document class must be set before using \'getSyncStorage\'');
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
    public function setSyncStorage(SyncStorageInterface $syncStorage)
    {
        $this->syncStorage = $syncStorage;

        return $this;
    }

    /**
     * @return array
     */
    protected function getSyncStorageData()
    {
        return $this->syncStorageData;
    }

    /**
     * @param array $syncStorageData
     *
     * @return $this
     */
    protected function setSyncStorageData($syncStorageData)
    {
        $this->syncStorageData = $syncStorageData;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function setItem(ItemPipelineEvent $event)
    {
        if (!parent::setItem($event)) {
            return false;
        }

        /** @var SyncExecuteItem $item */
        $item = $this->getItem();
        if (!$item instanceof SyncExecuteItem) {
            return false;
        }

        $tempSyncStorageData = $item->getSyncStorageData();

        if (!isset($tempSyncStorageData['type'])) {
            $this->log(
                sprintf('No operation type defined for document id: %s', $item->getDocument()->getId()),
                LogLevel::ERROR
            );

            return false;
        }
        $this->setSyncStorageData($tempSyncStorageData);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function persistDocument()
    {
        $syncStorageData = $this->getSyncStorageData();
        switch ($syncStorageData['type']) {
            case ActionTypes::CREATE:
                $this->getElasticsearchManager()->persist($this->getItem()->getDocument());
                break;
            case ActionTypes::UPDATE:
                $this->getElasticsearchManager()->persist($this->getItem()->getDocument());
                break;
            case ActionTypes::DELETE:
                $this->getElasticsearchManager()->getRepository($this->getDocumentClass())->remove(
                    $this->getItem()->getDocument()->getId()
                );
                break;
            default:
                $this->log(
                    sprintf(
                        'Failed to update document of type  %s id: %s: no valid operation type defined',
                        get_class($this->getItem()->getDocument()),
                        $this->getItem()->getDocument()->getId()
                    )
                );

                return false;
        }
        $this->getSyncStorage()->deleteItem(
            $syncStorageData['id'],
            [$syncStorageData['shop_id']]
        );

        return true;
    }
}
