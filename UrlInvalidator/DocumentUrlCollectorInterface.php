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

use ONGR\RouterBundle\Document\SeoAwareInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Interface for document url collectors.
 */
interface DocumentUrlCollectorInterface
{
    /**
     * Get all possible document URLs.
     *
     * @param string                          $type
     * @param SeoAwareInterface               $document
     * @param RouterInterface                 $router
     *
     * @return array
     */
    public function getDocumentUrls($type, SeoAwareInterface $document, RouterInterface $router);

    /**
     * Get related documents parameters.
     *
     * @param string        $type
     * @param SeoAwareInterface $document
     *
     * @return array
     */
    public function getDocumentParameters($type, SeoAwareInterface $document);

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
