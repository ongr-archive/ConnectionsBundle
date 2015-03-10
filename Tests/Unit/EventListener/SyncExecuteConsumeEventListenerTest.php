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

use ONGR\ConnectionsBundle\EventListener\SyncExecuteConsumeEventListener;
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\Pipeline\Item\SyncExecuteItem;
use ONGR\ConnectionsBundle\Sync\ActionTypes;
use ONGR\ConnectionsBundle\Tests\Functional\Fixtures\Bundles\Acme\TestBundle\Document\Product;
use ONGR\ConnectionsBundle\Tests\Functional\Fixtures\ImportCommandTest\TestProduct;
use Psr\Log\LogLevel;

class SyncExecuteConsumeEventListenerTest extends \PHPUnit_Framework_TestCase
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

        $syncStorage = $this->getMockBuilder('ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorage')
            ->disableOriginalConstructor()
            ->getMock();

        if ($managerMethod !== null) {
            $manager->expects($this->once())
                ->method($managerMethod);
        }

        $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->setMethods(['log'])
            ->getMockForAbstractClass();

        $paramsArrays = [];

        foreach ($loggerNotice as $notice) {
            $paramsArrays[] = [$notice[1], $this->equalTo($notice[0]), []];
        }
        call_user_func_array(
            [
                $logger->expects($this->exactly(count($paramsArrays)))->method('log'),
                'withConsecutive',
            ],
            $paramsArrays
        );

        $listener = new SyncExecuteConsumeEventListener($manager, $documentType, $syncStorage, 1);
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
        $product = new Product();
        $documentId = '123';
        $product->setId($documentId);

        return [
            [
                'document_type' => 'product',
                'event_item' => new SyncExecuteItem(
                    new TestProduct(),
                    $product,
                    [
                        'type' => ActionTypes::DELETE,
                        'id' => 1,
                        'shop_id' => 1,
                    ]
                ),
                'logger_notice' => [
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
                'managerMethod' => 'getRepository',
            ],
            [
                'document_type' => 'product',
                'event_item' => new SyncExecuteItem(
                    new TestProduct(),
                    $product,
                    [
                        'type' => ActionTypes::UPDATE,
                        'id' => 1,
                        'shop_id' => 1,
                    ]
                ),
                'logger_notice' => [
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
                'managerMethod' => 'persist',
            ],
            [
                'document_type' => 'product',
                'event_item' => new SyncExecuteItem(
                    new TestProduct(),
                    $product,
                    [
                        'type' => ActionTypes::CREATE,
                        'id' => 1,
                        'shop_id' => 1,
                    ]
                ),
                'logger_notice' => [
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
                'managerMethod' => 'persist',
            ],
            [
                'document_type' => 'product',
                'event_item' => new SyncExecuteItem(new TestProduct(), $product, ['type' => '']),
                'logger_notice' => [
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
                            'Failed to update document of type  %s id: %s: no valid operation type defined',
                            get_class($product),
                            $product->getId()
                        ),
                        LogLevel::DEBUG,
                    ],
                ],
                'managerMethod' => null,
            ],
            [
                'document_type' => 'product',
                'event_item' => new SyncExecuteItem(new TestProduct(), $product, []),
                'logger_notice' => [["No operation type defined for document id: {$documentId}", LogLevel::ERROR]],
                'managerMethod' => null,
            ],
            [
                'document_type' => 'product',
                'event_item' => new \stdClass,
                'logger_notice' => [
                    ['Item provided is not an ONGR\ConnectionsBundle\Pipeline\Item\SyncExecuteItem', LogLevel::ERROR],
                ],
                'managerMethod' => null,
            ],
        ];
    }
}
