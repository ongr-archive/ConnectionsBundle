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
use ONGR\ConnectionsBundle\Pipeline\Item\AbstractImportItem;
use ONGR\ElasticsearchBundle\Document\DocumentInterface;

/**
 * Basic implementation of modifier. Only copies matching properties.
 */
class ModifyEventListener extends AbstractImportModifyEventListener
{
    /**
     * @var string[]
     */
    private $copySkipFields = [];

    /**
     * @return string[]
     */
    public function getCopySkipFields()
    {
        return $this->copySkipFields;
    }

    /**
     * @param string[] $copySkipFields
     *
     * @return $this
     */
    public function setCopySkipFields($copySkipFields)
    {
        $this->copySkipFields = $copySkipFields;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function modify(AbstractImportItem $eventItem, ItemPipelineEvent $event)
    {
        $this->transform(
            $eventItem->getDocument(),
            $eventItem->getEntity()
        );
    }

    /**
     * Copies properties that have matching getter and setter.
     *
     * @param DocumentInterface $document
     * @param object            $entity
     * @param string[]          $skip
     */
    protected function transform(DocumentInterface $document, $entity, $skip = null)
    {
        $entityMethods = get_class_methods($entity);
        $documentMethods = get_class_methods($document);
        if ($skip === null) {
            $skip = $this->getCopySkipFields();
        }

        foreach ($entityMethods as $method) {
            if (strpos($method, 'get') !== 0) {
                continue;
            }
            $property = substr($method, 3);
            if (in_array(lcfirst($property), $skip)) {
                continue;
            }
            $setter = 'set' . $property;
            if (in_array($setter, $documentMethods)) {
                $document->{$setter}($entity->{$method}());
            }
        }
    }
}
