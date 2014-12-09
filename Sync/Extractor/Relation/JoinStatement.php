<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync\Extractor\Relation;

use ONGR\ConnectionsBundle\Sync\JobTableFields;

/**
 * This class creates sql statement for inserting multiple jobs by related table.
 */
class JoinStatement extends AbstractJoinStatement
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $documentId;

    /**
     * @var string
     */
    protected $searchCondition;

    /**
     * @param string $table           Related table name.
     * @param string $documentId      Document id.
     * @param string $searchCondition Escaped condition to create where sentence.
     * @param string $documentType    Target document type.
     */
    public function __construct(
        $table,
        $documentId,
        $searchCondition,
        $documentType
    ) {
        $this->table = $table;
        $this->documentId = $documentId;
        $this->searchCondition = $searchCondition;
        parent::__construct($documentType);
    }

    /**
     * {@inheritdoc}
     */
    public function getSelectQuery()
    {
        $select = sprintf(
            "SELECT '%s' as '%s', %s as '%s' FROM %s WHERE %s;",
            $this->documentType,
            JobTableFields::TYPE,
            $this->documentId,
            JobTableFields::ID,
            $this->table,
            $this->searchCondition
        );

        return $select;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentId()
    {
        return $this->documentId;
    }

    /**
     * @param string $documentId
     */
    public function setDocumentId($documentId)
    {
        $this->documentId = $documentId;
    }
}
