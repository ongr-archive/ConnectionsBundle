<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\app\fixture\Acme\TestBundle\Document;

use ONGR\ElasticsearchBundle\Annotation as ES;
use ONGR\ElasticsearchBundle\Document\Suggester\CompletionSuggesterInterface;
use ONGR\ElasticsearchBundle\Document\Suggester\CompletionSuggesterTrait;

/**
 * Suggesting document for testing.
 *
 * @ES\Object
 */
class CompletionSuggesting implements CompletionSuggesterInterface
{
    use CompletionSuggesterTrait;
}
