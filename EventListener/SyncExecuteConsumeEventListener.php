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
use ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorage;
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
    protected $documentType;

    /**
     * @var SyncStorage
     */
    protected $syncStorage;

    /**
     * @param Manager     $manager
     * @param string      $documentType
     * @param SyncStorage $syncStorage
     */
    public function __construct(Manager $manager, $documentType, SyncStorage $syncStorage)
    {
        $this->documentType = $documentType;
        $this->syncStorage = $syncStorage;
        parent::__construct($manager, 'ONGR\ConnectionsBundle\Import\Item\SyncExecuteItem');
    }

    /**
     * {@inheritdoc}
     */
    protected function setItem(ItemPipelineEvent $event)
    {
        if (!parent::setItem($event)) {
            return false;
        }

        if (!isset($this->importItem->getSyncStorageData()['type'])) {
            $this->log(
                sprintf('No operation type defined for document id: %s', $this->importItem->getDocument()->getId()),
                LogLevel::NOTICE
            );

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function persistDocument()
    {
        switch ($this->importItem->getSyncStorageData()['type']) {
            case SyncStorageInterface::OPERATION_CREATE:
                $this->manager->persist($this->importItem->getDocument());
                break;
            case SyncStorageInterface::OPERATION_UPDATE:
                $this->manager->persist($this->importItem->getDocument());
                break;
            case SyncStorageInterface::OPERATION_DELETE:
                $this->manager->getRepository($this->documentType)->remove($this->importItem->getDocument()->getId());
                break;
            default:
                $this->log(
                    sprintf(
                        'Failed to update document of type  %s id: %s: no valid operation type defined',
                        get_class($this->importItem->getDocument()),
                        $this->importItem->getDocument()->getId()
                    )
                );

                return false;
        }
        $this->syncStorage->deleteItem(
            $this->importItem->getSyncStorageData()['id'],
            [$this->importItem->getSyncStorageData()['shop_id']]
        );

        return true;
    }
}
