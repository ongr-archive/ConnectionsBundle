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

use ONGR\ConnectionsBundle\Event\SyncExecuteConsumeEvent;
use ONGR\ConnectionsBundle\Event\SyncExecuteItem;
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\Sync\Panther\PantherInterface;
use ONGR\ConnectionsBundle\Tests\Functional\Fixtures\ImportCommandTest\TestProduct;
use ONGR\TestingBundle\Document\Product;
use Psr\Log\LogLevel;

class SyncExecuteConsumeEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests what notices are provided to logger in different cases.
     *
     * @param string $documentType
     * @param mixed  $eventItem
     * @param array  $loggerNotice
     * @param string $managerMethod
     *
     * @dataProvider onConsumeDataProvider
     */
    public function testOnConsume($documentType, $eventItem, $loggerNotice, $managerMethod)
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
            ->setMethods(['log'])
            ->getMockForAbstractClass();

        // Check how many times log is called.
        switch (count($loggerNotice)) {
            case 1:
                $logger->expects($this->once())
                    ->method('log')
                    ->withConsecutive(
                        [$loggerNotice[0][1], $this->equalTo($loggerNotice[0][0]), []]
                    );
                break;
            case 2:
                $logger->expects($this->exactly(2))
                    ->method('log')
                    ->withConsecutive(
                        [$loggerNotice[0][1], $this->equalTo($loggerNotice[0][0]), []],
                        [$loggerNotice[1][1], $this->equalTo($loggerNotice[1][0]), []]
                    );
                break;
            case 3:
                $logger->expects($this->exactly(3))
                    ->method('log')
                    ->withConsecutive(
                        [$loggerNotice[0][1], $this->equalTo($loggerNotice[0][0]), []],
                        [$loggerNotice[1][1], $this->equalTo($loggerNotice[1][0]), []],
                        [$loggerNotice[2][1], $this->equalTo($loggerNotice[2][0]), []]
                    );
                break;
            default:
                // Do nothing.
                break;
        }

        $event = new SyncExecuteConsumeEvent($manager, $documentType, $panther, 1);
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
                new SyncExecuteItem(
                    new TestProduct(),
                    $product,
                    [
                        'type' => PantherInterface::OPERATION_DELETE,
                        'id' => 1,
                        'shop_id' => 1,
                    ]
                ),
                [
                    [
                        sprintf(
                            'Start update single document of type %s id: %s',
                            get_class($product),
                            $product->getId()
                        ),
                        LogLevel::DEBUG,
                    ],
                    [
                        'End an update of a single document.',
                        LogLevel::DEBUG,
                    ],
                ],
                'getRepository',
            ],
            [
                'product',
                new SyncExecuteItem(
                    new TestProduct(),
                    $product,
                    [
                        'type' => PantherInterface::OPERATION_UPDATE,
                        'id' => 1,
                        'shop_id' => 1,
                    ]
                ),
                [
                    [
                        sprintf(
                            'Start update single document of type %s id: %s',
                            get_class($product),
                            $product->getId()
                        ),
                        LogLevel::DEBUG,
                    ],
                    [
                        'End an update of a single document.',
                        LogLevel::DEBUG,
                    ],
                ],
                'persist',
            ],
            [
                'product',
                new SyncExecuteItem(
                    new TestProduct(),
                    $product,
                    [
                        'type' => PantherInterface::OPERATION_CREATE,
                        'id' => 1,
                        'shop_id' => 1,
                    ]
                ),
                [
                    [
                        sprintf(
                            'Start update single document of type %s id: %s',
                            get_class($product),
                            $product->getId()
                        ),
                        LogLevel::DEBUG,
                    ],
                    [
                        'End an update of a single document.',
                        LogLevel::DEBUG,
                    ],
                ],
                'persist',
            ],
            [
                'product',
                new SyncExecuteItem(new TestProduct(), $product, ['type' => '']),
                [
                    [
                        sprintf(
                            'Start update single document of type %s id: %s',
                            get_class($product),
                            $product->getId()
                        ),
                        LogLevel::DEBUG,
                    ],
                    [
                        sprintf(
                            'Failed to update document of type  %s id: %s',
                            get_class($product),
                            $product->getId()
                        ),
                        LogLevel::DEBUG,
                    ],
                    ["No valid operation type defined for document id: {$documentId}", LogLevel::NOTICE],
                ],
                null,
            ],
            [
                'product',
                new SyncExecuteItem(new TestProduct(), $product, []),
                [["No operation type defined for document id: {$documentId}", LogLevel::NOTICE]],
                null,
            ],
            [
                'product',
                new SyncExecuteItem(new TestProduct(), new Product(), []),
                [['No document id found. Update skipped.', LogLevel::NOTICE]],
                null,
            ],
            [
                'product',
                new \stdClass,
                [['Item provided is not an SyncImportItem', LogLevel::NOTICE]],
                null,
            ],
        ];
    }
}
