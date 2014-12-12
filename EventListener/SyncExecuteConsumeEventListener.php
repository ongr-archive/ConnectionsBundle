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

use ONGR\ConnectionsBundle\Import\Item\SyncExecuteItem;
use ONGR\ConnectionsBundle\Log\EventLoggerAwareTrait;
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorage;
use ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorageInterface;
use ONGR\ElasticsearchBundle\Document\DocumentInterface;
use ONGR\ElasticsearchBundle\ORM\Manager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;

/**
 * SyncExecuteConsumeEventListener class, called after modify event.
 *
 * Puts/updates or deletes document into/from Elasticsearch.
 */
class SyncExecuteConsumeEventListener implements LoggerAwareInterface
{
    use EventLoggerAwareTrait;

    /**
     * @var Manager $manager
     */
    protected $manager;

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
        $this->manager = $manager;
        $this->documentType = $documentType;
        $this->syncStorage = $syncStorage;
    }

    /**
     * Consume event.
     *
     * @param ItemPipelineEvent $event
     *
     * @return bool
     */
    public function onConsume(ItemPipelineEvent $event)
    {
        $item = $event->getItem();

        if (!$item instanceof SyncExecuteItem) {
            $this->log('Item provided is not an SyncExecuteItem', LogLevel::NOTICE);

            return false;
        }

        /** @var DocumentInterface $document */
        $document = $event->getItem()->getDocument();

        if ($document->getId() === null) {
            $this->log('No document id found. Update skipped.', LogLevel::NOTICE);

            return false;
        }

        $syncStorageData = $item->getSyncStorageData();
        if (!isset($syncStorageData['type'])) {
            $this->log(sprintf('No operation type defined for document id: %s', $document->getId()), LogLevel::NOTICE);

            return false;
        }

        $this->log(sprintf('Start update single document of type %s id: %s', get_class($document), $document->getId()));

        switch ($syncStorageData['type']) {
            case SyncStorageInterface::OPERATION_CREATE:
                $this->manager->persist($document);
                break;
            case SyncStorageInterface::OPERATION_UPDATE:
                $this->manager->persist($document);
                break;
            case SyncStorageInterface::OPERATION_DELETE:
                $this->manager->getRepository($this->documentType)->remove($document->getId());
                break;
            default:
                $this->log(
                    sprintf('Failed to update document of type  %s id: %s', get_class($document), $document->getId())
                );
                $this->log(
                    sprintf('No valid operation type defined for document id: %s', $document->getId()),
                    LogLevel::NOTICE
                );

                return false;
        }
        $this->syncStorage->deleteItem($syncStorageData['id'], [$syncStorageData['shop_id']]);

        $this->log('End an update of a single document.');

        return true;
    }
}
