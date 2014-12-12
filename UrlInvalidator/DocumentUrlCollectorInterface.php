<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\UrlInvalidator;

use ONGR\ElasticsearchBundle\Document\DocumentInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Interface DocumentUrlCollectorInterface.
 */
interface DocumentUrlCollectorInterface
{
    /**
     * Get all possible document URLs.
     *
     * @param string            $type
     * @param DocumentInterface $document
     * @param RouterInterface   $router
     *
     * @return array
     */
    public function getDocumentUrls($type, DocumentInterface $document, RouterInterface $router);

    /**
     * Get related documents parameters.
     *
     * @param string            $type
     * @param DocumentInterface $document
     *
     * @return array
     */
    public function getDocumentParameters($type, DocumentInterface $document);

    /**
     * Get URLs related to type.
     *
     * @param string          $type
     * @param RouterInterface $router
     *
     * @return array
     */
    public function getUrlsByType($type, RouterInterface $router);
}
