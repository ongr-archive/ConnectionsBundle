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

/**
 * Stores all registered sql relations.
 */
class RelationsCollection
{
    /**
     * @var SqlRelationInterface[]
     */
    private $relations;

    /**
     * @param SqlRelationInterface $relation
     */
    public function addRelation(SqlRelationInterface $relation)
    {
        $this->relations[] = $relation;
    }

    /**
     * @return SqlRelationInterface[]
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * @param SqlRelationInterface[] $relations
     */
    public function setRelations($relations)
    {
        $this->relations = $relations;
    }
}
