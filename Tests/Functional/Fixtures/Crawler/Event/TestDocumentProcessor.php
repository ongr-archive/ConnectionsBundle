<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\Fixtures\Crawler\Event;

use ONGR\ConnectionsBundle\EventListener\AbstractCrawlerModifier;

/**
 * Class TestDocumentProcessor - you know, for tests.
 */
class TestDocumentProcessor extends AbstractCrawlerModifier
{
    /**
     * @var array|documentCollection Stores returned documents.
     */
    public $documentCollection;

    /**
     * Processes single document.
     *
     * @param mixed $document
     */
    public function processData($document)
    {
        $this->documentCollection[] = $document;
    }
}
