<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Event;

use ONGR\ElasticsearchBundle\Document\DocumentInterface;

/**
 * Import event item carrying both Doctrine element and ES element.
 */
class SyncImportItem extends AbstractImportItem
{
    /**
     * @var array $pantherData
     */
    protected $pantherData;

    /**
     * @param mixed             $doctrineItem
     * @param DocumentInterface $elasticItem
     * @param array             $pantherData
     */
    public function __construct($doctrineItem, DocumentInterface $elasticItem, $pantherData)
    {
        parent::__construct($doctrineItem, $elasticItem);
        $this->pantherData = $pantherData;
    }

    /**
     * @return array
     */
    public function getPantherData()
    {
        return $this->pantherData;
    }

    /**
     * @param array $pantherData
     *
     * @return void
     */
    public function setPantherData($pantherData)
    {
        $this->pantherData = $pantherData;
    }
}
