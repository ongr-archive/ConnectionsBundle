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

use ONGR\ConnectionsBundle\Sync\ActionTypes;
use ONGR\ConnectionsBundle\Sync\DiffProvider\SyncJobs\SyncTrait;
use ONGR\ConnectionsBundle\Sync\JobTableFields;

/**
 * Class for managing simple triggers.
 */
class SqlRelation implements SqlRelationInterface
{
    use SyncTrait;

    /**
     * Update full document
     */
    const TYPE_FULL = 1;

    /**
     * Update document only with information from main entity
     */
    const TYPE_PARTIAL = 0;

    /**
     * @var string Trigger type.
     */
    protected $type;

    /**
     * @var string Alias for trigger type.
     */
    protected $typeAlias;

    /**
     * @var array Valid trigger types.
     */
    protected $validTypes;

    /**
     * @var string SQL script which will be returned to create trigger.
     */
    protected $sqlScript;

    /**
     * @var array SQL trigger insertion list placeholder.
     */
    protected $sqlInsertList;

    /**
     * @var string Table for trigger.
     */
    protected $table;

    /**
     * @var string Trigger name.
     */
    protected $triggerName;

    /**
     * @var string SQL trigger structure holder.
     */
    protected $sqlSkeleton;

    /**
     * @var array Fields that will be watched for changes on update trigger.
     */
    protected $updateFields;

    /**
     * @var int Default job type if none is set.
     */
    protected $defaultJobType = self::TYPE_PARTIAL;

    /**
     * @var JoinStatementInterface[]
     */
    protected $statements = [];

    /**
     * @var string Name of the relation.
     */
    private $name;

    /**
     * Constructor.
     *
     * @param string      $table        Table name to hook on.
     * @param string      $type         Trigger and default job type C - create, U - update,  D - delete.
     * @param int|null    $idField      Source for document id.
     * @param int|null    $updateType   Partial update - 0, full update - 1.
     * @param string|null $documentType Type of target document.
     * @param array       $trackFields  Array of table fields to track, all using default priority.
     * @param string|null $jobType      C - create, U - update,  D - delete.
     */
    public function __construct(
        $table = null,
        $type = null,
        $idField = null,
        $updateType = self::TYPE_PARTIAL,
        $documentType = null,
        $trackFields = [],
        $jobType = null
    ) {
        $this->sqlInsertList = [];
        $this->updateFields = [];
        $this->sqlSkeleton = '%sCREATE TRIGGER %s AFTER %s ON `%s` FOR EACH ROW BEGIN %s END;%s';
        $this->validTypes = [
            ActionTypes::CREATE => 'INSERT',
            ActionTypes::UPDATE => 'UPDATE',
            ActionTypes::DELETE => 'DELETE',
        ];
        $this->table = $table;
        $this->type = isset($type) ? $this->validTypes[$type] : null;
        $this->typeAlias = isset($type) ? $type : null;
        $this->defaultJobType = $updateType;
        isset($documentType) && $this->addToInsertList(JobTableFields::TYPE, $documentType);
        isset($idField) && $this->addToInsertList(JobTableFields::ID, $idField, false);
        isset($idField) && $this->addToInsertList(JobTableFields::TIMESTAMP, 'NOW()', false);
        array_walk($trackFields, [$this, 'addToUpdateFields']);
        $this->typeAlias = isset($jobType) ? $jobType : $this->typeAlias;
    }

    /**
     * Returns update fields.
     *
     * @return array
     */
    public function getUpdateFields()
    {
        return $this->updateFields;
    }

    /**
     * Adds to update fields.
     *
     * @param string   $updateField
     * @param int|null $updateType
     */
    public function addToUpdateFields($updateField, $updateType = null)
    {
        $this->updateFields[$updateField] = ['priority' => isset($updateType) ? $updateType : $this->defaultJobType];
    }

    /**
     * Sets default job type.
     *
     * @param int $defaultJobType
     */
    public function setDefaultJobType($defaultJobType)
    {
        $this->defaultJobType = $defaultJobType;
    }

    /**
     * Set update fields.
     *
     * @param array $updateFields
     */
    public function setUpdateFields($updateFields)
    {
        $this->updateFields = $updateFields;
    }

    /**
     * Forms sql insert list for trigger callback.
     *
     * @param mixed $key
     * @param mixed $value
     * @param bool  $isString
     */
    public function addToInsertList($key, $value, $isString = true)
    {
        $this->sqlInsertList[$key] = [
            'value' => $value,
            'string' => $isString,
        ];
    }

    /**
     * Returns insert list.
     *
     * @return array
     */
    public function getSqlInsertList()
    {
        return $this->sqlInsertList;
    }

    /**
     * Returns table being worked on.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Sets table to work on.
     *
     * @param mixed $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * Returns trigger name.
     *
     * @return mixed
     */
    public function getTriggerName()
    {
        return $this->triggerName;
    }

    /**
     * Sets trigger name.
     *
     * @param mixed $triggerName
     */
    public function setTriggerName($triggerName)
    {
        $this->triggerName = $triggerName;
    }

    /**
     * Sets trigger type.
     *
     * {@inheritdoc}
     */
    public function setTriggerType($type)
    {
        if (!array_key_exists($type, $this->validTypes)) {
            throw new \InvalidArgumentException('The type MUST be one of:' . implode(',', $this->validTypes));
        }

        $this->type = $this->validTypes[$type];
        $this->typeAlias = $type;
    }

    /**
     * Returns trigger type.
     *
     * @return string
     */
    public function getTriggerType()
    {
        return $this->type;
    }

    /**
     * Returns trigger type alias.
     *
     * @return string
     */
    public function getTriggerTypeAlias()
    {
        return $this->typeAlias;
    }

    /**
     * @param JoinStatementInterface $statement
     */
    public function addStatement(JoinStatementInterface $statement)
    {
        $this->statements[] = $statement;
    }

    /**
     * @return JoinStatementInterface[]
     */
    public function getStatements()
    {
        return $this->statements;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }
}
