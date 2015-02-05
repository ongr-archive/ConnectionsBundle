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
 * This interface defines structure for generating job insert statement.
 */
interface RelationInterface
{
    /**
     * Returns select query.
     *
     * @return string
     */
    public function getSelectQuery();

    /**
     * @return string
     */
    public function getDocumentType();

    /**
     * @return string
     */
    public function getDocumentId();
}
