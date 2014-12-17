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

use ONGR\ElasticsearchBundle\ORM\Manager;
use Psr\Log\LoggerAwareInterface;

/**
 * ImportConsumeEventListener class, called after modify event. Puts document into Elasticsearch.
 */
class ImportConsumeEventListener extends AbstractImportConsumeEventListener implements LoggerAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function __construct(Manager $manager)
    {
        parent::__construct($manager, 'ONGR\ConnectionsBundle\Import\Item\ImportItem');
    }
}
