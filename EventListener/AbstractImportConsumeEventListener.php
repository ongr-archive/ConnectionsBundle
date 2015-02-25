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

use ONGR\ConnectionsBundle\Log\EventLoggerAwareTrait;
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\Pipeline\Item\AbstractImportItem;
use ONGR\ElasticsearchBundle\ORM\Manager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;

/**
 * AbstractImportConsumeEventListener -  called after modify event. Puts document into Elasticsearch.
 */
abstract class AbstractImportConsumeEventListener extends AbstractConsumeEventListener implements LoggerAwareInterface
{
    use EventLoggerAwareTrait;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var string Import item class of an item contained in ItemPipelineEvent.
     */
    protected $importItemClass;

    /**
     * @var AbstractImportItem
     */
    protected $importItem;

    /**
     * @param Manager $manager
     * @param string  $itemClass
     */
    public function __construct(Manager $manager, $itemClass)
    {
        $this->manager = $manager;
        $this->importItemClass = $itemClass;
    }

    /**
     * Consume event.
     *
     * @param ItemPipelineEvent $event
     */
    public function consume(ItemPipelineEvent $event)
    {
        if (!$this->setItem($event)) {
            return;
        }

        $this->log(
            sprintf(
                'Start update single document of type %s id: %s',
                get_class($this->importItem->getDocument()),
                $this->importItem->getDocument()->getId()
            )
        );

        if (!$this->persistDocument()) {
            return;
        };

        $this->log('End an update of a single document.');
    }

    /**
     * Persist document to Elasticsearch.
     *
     * @return bool
     */
    protected function persistDocument()
    {
        $this->manager->persist($this->importItem->getDocument());

        return true;
    }

    /**
     * Validates the class name of event item and prepares internal document for persistence operation.
     *
     * @param ItemPipelineEvent $event
     *
     * @return bool
     */
    protected function setItem(ItemPipelineEvent $event)
    {
        /** @var AbstractImportItem $tempItem */
        $tempItem = $event->getItem();

        if (!$tempItem instanceof $this->importItemClass) {
            $this->log("Item provided is not an {$this->importItemClass}", LogLevel::ERROR);

            return false;
        }

        if ($tempItem->getDocument()->getId() === null) {
            $this->log('No document id found. Update skipped.', LogLevel::NOTICE);

            return false;
        }
        $this->importItem = $tempItem;

        return true;
    }
}
