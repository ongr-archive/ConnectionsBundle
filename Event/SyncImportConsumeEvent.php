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
use ONGR\ConnectionsBundle\Sync\Panther\Panther;
use ONGR\ConnectionsBundle\Sync\Panther\PantherInterface;
use ONGR\ElasticsearchBundle\Document\DocumentInterface;
use ONGR\ElasticsearchBundle\ORM\Manager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;

/**
 * SyncImportConsumeEvent class, called after modify event. Puts/updates or deletes document into/from Elasticsearch.
 */
class SyncImportConsumeEvent implements LoggerAwareInterface
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
     * @var Panther
     */
    protected $panther;

    /**
     * @param Manager $manager
     * @param string  $documentType
     * @param Panther $panther
     */
    public function __construct(Manager $manager, $documentType, Panther $panther)
    {
        $this->manager = $manager;
        $this->documentType = $documentType;
        $this->panther = $panther;
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

        if (!$item instanceof SyncImportItem) {
            $this->log('Item provided is not an SyncImportItem', LogLevel::NOTICE);

            return false;
        }

        /** @var DocumentInterface $document */
        $document = $event->getItem()->getDocument();

        if ($document->getId() === null) {
            $this->log('No document id found. Update skipped.', LogLevel::NOTICE);

            return false;
        }

        $pantherData = $item->getPantherData();
        if (!isset($pantherData['type'])) {
            $this->log(sprintf('No operation type defined for document id: %s', $document->getId()), LogLevel::NOTICE);

            return false;
        }

        $this->log(sprintf('Start update single document of type %s id: %s', get_class($document), $document->getId()));

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
                    $this->log(
                        sprintf(
                            'Failed to update document of type  %s id: %s',
                            get_class($document),
                            $document->getId()
                        )
                    );
                    $this->log(
                        sprintf('No valid operation type defined for document id: %s', $document->getId()),
                        LogLevel::NOTICE
                    );
                }

                return false;
        }
        $this->panther->deleteItem($pantherData['id'], [$pantherData['shop_id']]);

        $this->log('End an update of a single document.');

        return true;
    }
}
