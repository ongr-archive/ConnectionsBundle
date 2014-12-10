<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Event;

use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\Sync\Panther\PantherInterface;
use ONGR\ElasticsearchBundle\Document\DocumentInterface;
use ONGR\ElasticsearchBundle\ORM\Manager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * SyncImportConsumeEvent class, called after modify event. Puts/updates or deletes document into/from Elasticsearch.
 */
class SyncImportConsumeEvent implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Manager $manager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $documentType;

    /**
     * @param Manager $manager
     * @param string  $documentType
     */
    public function __construct(Manager $manager, $documentType)
    {
        $this->manager = $manager;
        $this->documentType = $documentType;
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
        if ($item instanceof SyncImportItem) {
            /** @var DocumentInterface $document */
            $document = $event->getItem()->getDocument();
        } else {
            if ($this->logger) {
                $this->logger->notice('Item provided is not an SyncImportItem');
            }

            return false;
        }

        if ($document->getId() === null) {
            if ($this->logger) {
                $this->logger->notice('No document id found. Update skipped.');
            }

            return false;
        }

        $pantherData = $item->getPantherData();
        if (isset($pantherData['type'])) {
            if ($this->logger) {
                $this->logger->debug(
                    'Start update single document of type ' . get_class($document) . ' id: ' . $document->getId()
                );
            }
            switch ($pantherData['type']) {
                case PantherInterface::OPERATION_CREATE:
                    $this->manager->persist($document);
                    break;
                case PantherInterface::OPERATION_UPDATE:
                    $this->manager->persist($document);
                    break;
                case PantherInterface::OPERATION_DELETE:
                    $this->manager->getRepository($this->documentType)->remove($document->getId());
                    break;
                default:
                    if ($this->logger) {
                        $this->logger->debug(
                            'Failed to update document of type ' . get_class($document) . ' id: ' . $document->getId()
                        );
                        $this->logger->notice(
                            'No valid operation type defined for document id:' . $document->getId()
                        );
                    }

                    return false;
            }
        } else {
            if ($this->logger) {
                $this->logger->notice(
                    'No operation type defined for document id:' . $document->getId()
                );
            }

            return false;
        }

        if ($this->logger) {
            $this->logger->debug(
                'End an update of a single document.'
            );
        }

        return true;
    }
}
