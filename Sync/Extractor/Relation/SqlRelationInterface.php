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
 * Interface for trigger.
 */
interface SqlRelationInterface
{
    /**
     * @param string $type
     *
     * @return void
     */
    public function setTriggerType($type);

    /**
     * @param string $name
     *
     * @return void
     */
    public function setTriggerName($name);

    /**
     * Table name setter that will be used for trigger.
     *
     * @param string $name
     *
     * @return void
     */
    public function setTable($name);

    /**
     * Returns trigger name used in DB.
     *
     * @return string
     */
    public function getTriggerName();
}
