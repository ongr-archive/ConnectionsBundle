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
use ONGR\ConnectionsBundle\Event\SyncImportConsumeEvent;
use ONGR\ConnectionsBundle\Event\SyncImportItem;
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\Sync\Panther\PantherInterface;
use ONGR\ConnectionsBundle\Tests\Functional\Fixtures\ImportCommandTest\TestProduct;
use ONGR\TestingBundle\Document\Product;

class SyncImportConsumeEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests what notices are provided to logger in different cases.
     *
     * @param string $documentType
     * @param mixed  $eventItem
     * @param string $loggerMethod
     * @param array  $loggerNotice
     * @param string $managerMethod
     *
     * @dataProvider onConsumeDataProvider
     */
    public function testOnConsume($documentType, $eventItem, $loggerMethod, $loggerNotice, $managerMethod)
    {
        $repo = $this->getMockBuilder('ONGR\ElasticsearchBundle\ORM\Repository')
            ->disableOriginalConstructor()
            ->setMethods(['remove'])
            ->getMock();

        $manager = $this->getMockBuilder('ONGR\ElasticsearchBundle\ORM\Manager')
            ->disableOriginalConstructor()
            ->setMethods(['persist', 'getRepository'])
            ->getMock();

        $manager->method('getRepository')
            ->willReturn($repo);

        $panther = $this->getMockBuilder('ONGR\ConnectionsBundle\Sync\Panther\Panther')
            ->disableOriginalConstructor()
            ->getMock();

        if ($managerMethod !== null) {
            $manager->expects($this->once())
                ->method($managerMethod);
        }

        $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->setMethods([$loggerMethod])
            ->getMockForAbstractClass();

        // Check if logger->notice() called 2 times with different messages.
        if (count($loggerNotice) == 2) {
            $logger->expects($this->exactly(2))
                ->method($loggerMethod)
                ->withConsecutive([$this->equalTo($loggerNotice[0])], [$this->equalTo($loggerNotice[1])]);
        } else {
            $logger->expects($this->once())
                ->method($loggerMethod)
                ->withConsecutive(
                    [$this->equalTo($loggerNotice[0])]
                );
        }

        $event = new SyncImportConsumeEvent($manager, $documentType, $panther, 1);
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
        $product = new Product();
        $documentId = '123';
        $product->setId($documentId);

        return [
            [
                'product',
                new SyncImportItem(
                    new TestProduct(),
                    $product,
                    [
                        'type' => PantherInterface::OPERATION_DELETE,
                        'id' => 1,
                        'shop_id' => 1,
                    ]
                ),
                'debug',
                [
                    sprintf('Start update single document of type %s id: %s', get_class($product), $product->getId()),
                    'End an update of a single document.',
                ],
                'getRepository',
            ],
            [
                'product',
                new SyncImportItem(
                    new TestProduct(),
                    $product,
                    [
                        'type' => PantherInterface::OPERATION_UPDATE,
                        'id' => 1,
                        'shop_id' => 1,
                    ]
                ),
                'debug',
                [
                    sprintf('Start update single document of type %s id: %s', get_class($product), $product->getId()),
                    'End an update of a single document.',
                ],
                'persist',
            ],
            [
                'product',
                new SyncImportItem(
                    new TestProduct(),
                    $product,
                    [
                        'type' => PantherInterface::OPERATION_CREATE,
                        'id' => 1,
                        'shop_id' => 1,
                    ]
                ),
                'debug',
                [
                    sprintf('Start update single document of type %s id: %s', get_class($product), $product->getId()),
                    'End an update of a single document.',
                ],
                'persist',
            ],
            [
                'product',
                new SyncImportItem(new TestProduct(), $product, ['type' => '']),
                'notice',
                ["No valid operation type defined for document id: {$documentId}"],
                null,
            ],
            [
                'product',
                new SyncImportItem(new TestProduct(), $product, []),
                'notice',
                ["No operation type defined for document id: {$documentId}"],
                null,
            ],
            [
                'product',
                new SyncImportItem(new TestProduct(), new Product(), []),
                'notice',
                ['No document id found. Update skipped.'],
                null,
            ],
            [
                'product',
                new \stdClass,
                'notice',
                ['Item provided is not an SyncImportItem'],
                null,
            ],
        ];
    }
}
