<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\UrlInvalidator;

use ONGR\ConnectionsBundle\Pipeline\PipelineFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Functional tests for AbstractItemUrlInvalidator.
 */
class ItemUrlInvalidatorTest extends WebTestCase
{
    /**
     * Test for AbstractItemUrlInvalidator::invalidateItem.
     */
    public function testInvalidateItem()
    {
        $kernel = self::createClient()->getKernel();

        $pipeline = $kernel->getContainer()->get('ongr_connections.pipeline_factory')->create(
            'dummypipeline.default',
            ['consumers' => [PipelineFactory::CONSUMER_RETURN]]
        );
        $pipeline->execute();

        $invalidator = $kernel->getContainer()->get('project.item_url_invalidator.dummy');

        $this->assertEquals(1, $invalidator->getFinishCalled());
        $this->assertEquals(3, $invalidator->getConsumeCalled());
    }
}
