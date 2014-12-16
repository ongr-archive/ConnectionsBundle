<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\Fixtures\Bundles\Acme\TestBundle\Document;

use ONGR\ElasticsearchBundle\Annotation as ES;

/**
 * UrlObject document for testing.
 *
 * @ES\Object
 */
class UrlObject
{
    /**
     * @var string
     *
     * @ES\Property(name="url", type="string")
     */
    private $url;

    /**
     * @var string
     *
     * @ES\Property(name="key", type="string", index="no")
     */
    private $urlKey;

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $urlKey
     */
    public function setUrlKey($urlKey)
    {
        $this->urlKey = $urlKey;
    }

    /**
     * @return string
     */
    public function getUrlKey()
    {
        return $this->urlKey;
    }
}
