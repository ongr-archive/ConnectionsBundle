<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity for data synchronization job.
 *
 * @ORM\Entity
 * @ORM\Table("@sync_jobs_table")
 */
class SyncJob
{
    const TYPE_CREATE = 'C';
    const TYPE_UPDATE = 'U';
    const TYPE_DELETE = 'D';

    const STATUS_NEW = 0;
    const STATUS_DONE = 1;

    const UPDATE_TYPE_PARTIAL = 0;
    const UPDATE_TYPE_FULL = 1;

    /**
     * @var int
     *
     * @ORM\Column
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column
     */
    protected $type;

    /**
     * @var int
     *
     * @ORM\Column(name="`status@active_shop`")
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(name="document_type")
     */
    protected $documentType;

    /**
     * @var string
     *
     * @ORM\Column(name="document_id")
     */
    protected $documentId;

    /**
     * @var int
     *
     * @ORM\Column(name="update_type")
     */
    protected $updateType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="`timestamp`", type="datetime")
     */
    protected $timestamp;

    /**
     * @var mixed
     */
    protected $documentData;

    /**
     * Returns entity ID.
     *
     * @return int
     *
     * @codeCoverageIgnore
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets record type.
     *
     * @param string $type
     *
     * @return SyncJob
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Returns record type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets record status.
     *
     * @param int $status
     *
     * @return SyncJob
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Returns record status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets document type.
     *
     * @param string $type
     *
     * @return SyncJob
     */
    public function setDocumentType($type)
    {
        $this->documentType = $type;

        return $this;
    }

    /**
     * Returns document type.
     *
     * @return string
     */
    public function getDocumentType()
    {
        return $this->documentType;
    }

    /**
     * Sets document ID.
     *
     * @param string $documentId
     *
     * @return SyncJob
     */
    public function setDocumentId($documentId)
    {
        $this->documentId = $documentId;

        return $this;
    }

    /**
     * Returns document ID.
     *
     * @return string
     */
    public function getDocumentId()
    {
        return $this->documentId;
    }

    /**
     * Sets update type.
     *
     * @param int $updateType
     *
     * @return SyncJob
     */
    public function setUpdateType($updateType)
    {
        $this->updateType = $updateType;

        return $this;
    }

    /**
     * Returns update type.
     *
     * @return int
     */
    public function getUpdateType()
    {
        return $this->updateType;
    }

    /**
     * Sets record timestamp.
     *
     * @param \DateTime $datetime
     *
     * @return SyncJob
     */
    public function setTimestamp(\DateTime $datetime)
    {
        $this->timestamp = $datetime;

        return $this;
    }

    /**
     * Returns record timestamp.
     *
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Sets document data.
     *
     * @param mixed $documentData
     *
     * @return SyncJob
     */
    public function setDocumentData($documentData)
    {
        $this->documentData = $documentData;

        return $this;
    }

    /**
     * Returns document data.
     *
     * @return mixed
     */
    public function getDocumentData()
    {
        return $this->documentData;
    }
}
