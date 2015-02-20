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
use ONGR\ElasticsearchBundle\Document\AbstractDocument;
use ONGR\RouterBundle\Document\SeoAwareInterface;
use ONGR\RouterBundle\Document\SeoAwareTrait;

/**
 * Seo aware document.
 *
 * @ES\Document(type="seo")
 */
class SeoDocument extends AbstractDocument implements SeoAwareInterface
{
    use SeoAwareTrait;
}
