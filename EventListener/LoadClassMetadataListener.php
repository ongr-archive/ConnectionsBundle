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

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Listens to mapping events and processes data replacements.
 */
class LoadClassMetadataListener
{
    /**
     * @var array
     */
    protected $replacements;

    /**
     * @param array $replacements
     */
    public function __construct(array $replacements = [])
    {
        $this->replacements = $replacements;
    }

    /**
     * Adds single replacement.
     *
     * @param string $search
     * @param string $replace
     */
    public function addReplacement($search, $replace)
    {
        $this->replacements[$search] = $replace;
    }

    /**
     * Processes doctrine mapping metadata and does replacements, as provided in replacements map.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        if (empty($this->replacements)) {
            return;
        }

        /** @var ClassMetadataInfo $metadata */
        $metadata = $eventArgs->getClassMetadata();

        // Handle table name.
        $tableName = $metadata->getTableName();
        if ($tableName) {
            $metadata->setPrimaryTable(
                [
                    'name' => $this->doReplacement($tableName),
                ]
            );
        }

        // Handle fields AKA columns.
        foreach ($metadata->getFieldNames() as $fieldName) {
            $mapping = $metadata->getFieldMapping($fieldName);
            foreach ($mapping as $key => $value) {
                if (is_string($value)) {
                    $mapping[$key] = $this->doReplacement($value);
                }
            }
            $metadata->setAttributeOverride($fieldName, $mapping);
        }

        // Handle associations AKA foreign keys.
        $associationMappings = $metadata->getAssociationMappings();
        foreach ($metadata->getAssociationNames() as $fieldName) {
            if (isset($associationMappings[$fieldName])) {
                $associationMapping = $associationMappings[$fieldName];
                if (isset($associationMapping['joinColumns'])) {
                    foreach ($associationMapping['joinColumns'] as $key => $joinColumn) {
                        $associationMapping['joinColumns'][$key]['name'] = $this->doReplacement($joinColumn['name']);
                        $associationMapping['joinColumns'][$key]['referencedColumnName'] = $this->doReplacement(
                            $joinColumn['referencedColumnName']
                        );
                    }
                    $metadata->setAssociationOverride($fieldName, $associationMapping);
                }
            }
        }

        // Handle discriminator.
        if (count($metadata->discriminatorMap)) {
            $this->processDiscriminatorMap($metadata);
        }
    }

    /**
     * Replace discriminator array key if needed.
     *
     * @param ClassMetadataInfo $metadata
     */
    protected function processDiscriminatorMap(ClassMetadataInfo $metadata)
    {
        $newMap = [];

        foreach ($metadata->discriminatorMap as $mapId => $mappedEntityName) {
            $newKey = $this->doReplacement($mapId);
            $newMap[$newKey] = $mappedEntityName;
        }

        $metadata->discriminatorMap = $newMap;
    }

    /**
     * Applies replacements to provided string.
     *
     * @param string $inputString
     *
     * @return string
     */
    protected function doReplacement($inputString)
    {
        if (is_string($inputString)) {
            $inputString = str_replace(
                array_keys($this->replacements),
                array_values($this->replacements),
                $inputString
            );
        }

        return $inputString;
    }
}
