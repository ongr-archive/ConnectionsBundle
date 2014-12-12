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

use ONGR\ConnectionsBundle\Log\EventLoggerAwareTrait;
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ElasticsearchBundle\Document\DocumentInterface;
use ONGR\ElasticsearchBundle\ORM\Manager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;

/**
 * ImportConsumeEvent class, called after modify event. Puts document into Elasticsearch.
 */
class ImportConsumeEvent implements LoggerAwareInterface
{
    use EventLoggerAwareTrait;

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
        $item = $event->getItem();

        if (!$item instanceof ImportItem) {
            $this->log('Item provided is not an ImportItem', LogLevel::NOTICE);

            return false;
        }

        /** @var DocumentInterface $document */
        $document = $event->getItem()->getDocument();

        if ($document->getId() === null) {
            $this->log('No document id found. Update skipped.', LogLevel::NOTICE);

            return false;
        }

        $this->log(sprintf('Start update single document of type %s id: %s', get_class($document), $document->getId()));

        $this->manager->persist($document);

        $this->log('End an update of a single document.');

        return true;
    }
}
