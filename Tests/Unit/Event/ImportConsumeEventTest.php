<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Event;

use ONGR\ConnectionsBundle\Event\ImportConsumeEvent;
use ONGR\ConnectionsBundle\Event\ImportItem;
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\Tests\Functional\Fixtures\ImportCommandTest\TestProduct;
use ONGR\TestingBundle\Document\Product;
use Psr\Log\LogLevel;

class ImportConsumeEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests what notices are provided to logger in different cases.
     *
     * @param mixed  $eventItem
     * @param string $notice
     *
     * @return void
     *
     * @dataProvider onConsumeDataProvider
     */
    public function testOnConsume($eventItem, $notice)
    {
        $manager = $this->getMockBuilder('ONGR\ElasticsearchBundle\ORM\Manager')
            ->disableOriginalConstructor()
            ->setMethods(['persist'])
            ->getMock();

        $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->setMethods(['log'])
            ->getMockForAbstractClass();
        $logger->expects($this->once())
            ->method('log')
            ->with(LogLevel::NOTICE, $this->equalTo($notice), []);

        $event = new ImportConsumeEvent($manager);

        $event->setLogger($logger);

        $pipelineEvent = new ItemPipelineEvent($eventItem);
        $event->onConsume($pipelineEvent);
    }

    /**
     * Provides data for testOnConsume test.
     *
     * @return array
     */
    public function onConsumeDataProvider()
    {
        return [
            [
                new ImportItem(new TestProduct(), new Product()),
                'No document id found. Update skipped.',
            ],
            [
                new \stdClass,
                'Item provided is not an ImportItem',
            ],
        ];
    }
}
