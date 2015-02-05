<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync\Extractor;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\AbstractQuery;
use ONGR\ConnectionsBundle\Sync\ActionTypes;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\BaseDiffItem;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\CreateDiffItem;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\DeleteDiffItem;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Item\UpdateDiffItem;
use ONGR\ConnectionsBundle\Sync\Extractor\Descriptor\ExtractionCollection;
use ONGR\ConnectionsBundle\Sync\Extractor\Descriptor\ExtractionDescriptorInterface;
use ONGR\ConnectionsBundle\Sync\JobTableFields;
use ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorageInterface;

/**
 * Extractor that joins entities for insertion to SyncStorage.
 */
class DoctrineExtractor extends AbstractExtractor implements ExtractorInterface
{
    /**
     * @var SyncStorageInterface
     */
    private $storage;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ExtractionCollection
     */
    private $extractionCollection;

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function extract(BaseDiffItem $item)
    {
        $connection = $this->getConnection();
        $descriptors = $this->getExtractionCollection()->getDescriptors();
        $action = $this->resolveItemAction($item);
        /** @var ExtractionDescriptorInterface $descriptor */
        foreach ($descriptors as $descriptor) {
            $table = $descriptor->getTable();
            if ($table === $item->getCategory() && $action === $descriptor->getTriggerTypeAlias()) {
                if ($action === ActionTypes::UPDATE
                    && !$this->isTrackedFieldModified($item, $descriptor)
                ) {
                    continue;
                }
                $insertList = $descriptor->getSqlInsertList();
                $idField = $insertList[JobTableFields::ID]['value'];

                $idFieldName = str_replace(['OLD.', 'NEW.'], '', $idField);
                $itemRow = $item->getItem();

                $statements = $descriptor->getRelations();

                if (isset($insertList[JobTableFields::TYPE]['value'])) {
                    $itemId = $itemRow[$idFieldName];

                    $storage = $this->getStorageFacility();
                    $storage->save(
                        $action,
                        $insertList[JobTableFields::TYPE]['value'],
                        $itemId,
                        $item->getTimestamp(),
                        $this->getShopIds()
                    );
                } elseif (empty($statements)) {
                    throw new \LogicException(
                        'Missing relations or no document type set in descriptor "'
                        . $descriptor->getName() . '"'
                    );
                }

                foreach ($statements as $statement) {
                    $selectQuery = $statement->getSelectQuery();
                    $sql = $this->inlineContext($selectQuery, $itemRow);
                    $executed = $connection->executeQuery($sql);
                    $this->saveResult($item, $executed);
                }
            }
        }
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param Connection $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorageFacility()
    {
        return $this->storage;
    }

    /**
     * {@inheritdoc}
     */
    public function setStorageFacility(SyncStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return ExtractionCollection
     */
    public function getExtractionCollection()
    {
        return $this->extractionCollection;
    }

    /**
     * @param ExtractionCollection $extractionCollection
     */
    public function setExtractionCollection($extractionCollection)
    {
        $this->extractionCollection = $extractionCollection;
    }

    /**
     * Returns action letter depending on item class.
     *
     * @param BaseDiffItem $item
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function resolveItemAction(BaseDiffItem $item)
    {
        if ($item instanceof CreateDiffItem) {
            $action = ActionTypes::CREATE;

            return $action;
        } elseif ($item instanceof DeleteDiffItem) {
            $action = ActionTypes::DELETE;

            return $action;
        } elseif ($item instanceof UpdateDiffItem) {
            $action = ActionTypes::UPDATE;

            return $action;
        } else {
            throw new \InvalidArgumentException('Unsupported diff item type. Got: ' . get_class($item));
        }
    }

    /**
     * Replace context placeholders with actual row values.
     *
     * @param string $selectQuery
     * @param array  $itemRow
     *
     * @return string
     */
    protected function inlineContext($selectQuery, $itemRow)
    {
        $selectQuery = str_replace(['OLD.', 'NEW.'], '__ctx__', $selectQuery);
        $prefixedKeys = array_map(
            function ($key) {
                return '__ctx__' . $key;
            },
            array_keys($itemRow)
        );
        $connection = $this->getConnection();
        $escapedValues = array_map(
            function ($value) use ($connection) {
                return $connection->quote($value);
            },
            array_values($itemRow)
        );
        $sql = str_replace($prefixedKeys, $escapedValues, $selectQuery);

        return $sql;
    }

    /**
     * Save results to storage.
     *
     * @param BaseDiffItem $item
     * @param Statement    $results
     * @param string       $action
     */
    protected function saveResult(BaseDiffItem $item, Statement $results, $action = 'U')
    {
        $storage = $this->getStorageFacility();
        while ($row = $results->fetch(AbstractQuery::HYDRATE_ARRAY)) {
            $storage->save(
                $action,
                $row[JobTableFields::TYPE],
                $row[JobTableFields::ID],
                $item->getTimestamp(),
                $this->getShopIds()
            );
        }
    }

    /**
     * Checks whether any of tracked fields has been modified.
     *
     * @param BaseDiffItem                  $item
     * @param ExtractionDescriptorInterface $relation
     *
     * @return bool
     *
     * @throws \InvalidArgumentException
     */
    private function isTrackedFieldModified(BaseDiffItem $item, ExtractionDescriptorInterface $relation)
    {
        if (!$item instanceof UpdateDiffItem) {
            throw new \InvalidArgumentException('Wrong diff item type. Got: ' . get_class($item));
        }

        $trackedFields = $relation->getUpdateFields();
        if (empty($trackedFields)) {
            return true;
        }

        $itemRow = $item->getItem();
        $oldItemRow = $item->getOldItem();

        foreach (array_keys($trackedFields) as $key) {
            if (array_key_exists($key, $itemRow) && $itemRow[$key] !== $oldItemRow[$key]) {
                return true;
            }
        }

        return false;
    }
}
