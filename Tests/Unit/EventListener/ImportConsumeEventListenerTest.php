<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\EventListener;

use ONGR\ConnectionsBundle\EventListener\ImportConsumeEventListener;
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\Pipeline\Item\ImportItem;
use ONGR\ConnectionsBundle\Tests\Functional\Fixtures\Bundles\Acme\TestBundle\Document\Product;
use ONGR\ConnectionsBundle\Tests\Functional\Fixtures\ImportCommandTest\TestProduct;
use Psr\Log\LogLevel;

class ImportConsumeEventListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests what notices are provided to logger in different cases.
     *
     * @param mixed  $eventItem
     * @param string $message
     * @param string $level
     *
     * @dataProvider onConsumeDataProvider
     */
    public function testOnConsume($eventItem, $message, $level)
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
            ->with($level, $this->equalTo($message), []);

        $listener = new ImportConsumeEventListener($manager);

        $listener->setLogger($logger);

        $pipelineEvent = new ItemPipelineEvent($eventItem);
        $listener->onConsume($pipelineEvent);
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
                new \stdClass,
                'Item provided is not an ONGR\ConnectionsBundle\Pipeline\Item\ImportItem',
                LogLevel::ERROR,
            ],
        ];
    }
}
