<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync\Extractor\Descriptor;

/**
 * Interface for extraction descriptor.
 */
interface ExtractorDescriptorInterface
{
    /**
     * @param string $type
     */
    public function setTriggerType($type);

    /**
     * @param string $name
     */
    public function setTriggerName($name);

    /**
     * Table name setter that will be used for trigger.
     *
     * @param string $name
     */
    public function setTable($name);

    /**
     * Sets name of the relation.
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Returns name of the relation.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns trigger name used in DB.
     *
     * @return string
     */
    public function getTriggerName();
}
