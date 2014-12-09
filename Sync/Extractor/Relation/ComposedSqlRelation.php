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
 * This class allows to create multiple jobs on single action.
 */
class ComposedSqlRelation extends SimpleSqlRelation
{
    /**
     * @var JoinStatementInterface[]
     */
    protected $statements = [];

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
}
