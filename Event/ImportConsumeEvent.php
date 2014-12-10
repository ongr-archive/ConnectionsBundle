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
use ONGR\ElasticsearchBundle\ORM\Manager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * ImportConsumeEvent class, called after modify event. Puts document into Elasticsearch.
 */
class ImportConsumeEvent implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
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
        if ($event instanceof ItemPipelineEvent) {
            $item = $event->getItem();
            if ($item instanceof ImportItem) {
                $document = $event->getItem()->getDocument();
            } else {
                if ($this->logger) {
                    $this->logger->notice('Item provided is not an ImportItem');
                }

                return false;
            }
        } else {
            if ($this->logger) {
                $this->logger->notice('Event provided is not an ItemPipelineEvent');
            }

            return false;
        }

        if ($document->getId() === null) {
            if ($this->logger) {
                $this->logger->notice('No document id found. Update skipped.');
            }

            return false;
        }

        if ($this->logger) {
            $this->logger->debug(
                'Start update single document of type ' . get_class($document) . ' id: ' . $document->getId()
            );
        }

        $this->manager->persist($document);

        if ($this->logger) {
            $this->logger->debug(
                'End an update of a single document.'
            );
        }

        return true;
    }
}
