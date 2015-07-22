<?php

/*
 * This file is part of the ONGR  package.
 *
 * c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\DependencyInjection;

use ONGR\ConnectionsBundle\DependencyInjection\ONGRConnectionsExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ONGRConnectionsExtensionTest.
 */
class ONGRConnectionsExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for testPipelineGeneration.
     *
     * @return array
     */
    public function pipelineGenerationProvider()
    {
        $cases = [];

        // Case #0. No Definitions.
        $cases[] = [
            'pipelineConfig' => [],
            'expectedDefinitions' => [],
        ];

        // Case #1. Default definition.
        $cases[] = [
            'pipelineConfig' => [
                'pipelines' => [
                    'test' => [
                        'types' => [
                            'content' => [
                                'entity_class' => 'TestBundle:Content',
                                'document_class' => 'TestBundle:Content',
                                'modifiers' => ['ONGR\ConnectionsBundle\EventListener\ModifyEventListener'],
                            ],
                        ],
                    ],
                ],
            ],
            'expectedDefinitions' => [
                'ongr_connections.pipelines.data_sync.test.source.0' => (new Definition(
                    'ONGR\ConnectionsBundle\EventListener\DataSyncSourceEventListener'
                ))->addTag(
                    'kernel.event_listener',
                    ['event' => 'ongr.pipeline.data_sync.test.source', 'method' => 'onSource']
                )->addMethodCall(
                    'setDiffProvider',
                    [new Reference('ongr_connections.sync.diff_provider.binlog_diff_provider')]
                ),

                'ongr_connections.pipelines.data_sync.test.consume.0' => (new Definition(
                    'ONGR\ConnectionsBundle\EventListener\DataSyncConsumeEventListener'
                ))->addTag(
                    'kernel.event_listener',
                    ['event' => 'ongr.pipeline.data_sync.test.consume', 'method' => 'onConsume']
                )->addMethodCall(
                    'setExtractor',
                    [new Reference('ongr_connections.sync.extractor.doctrine_extractor')]
                ),

                'ongr_connections.pipelines.import.test.content.source.0' => (new Definition(
                    'ONGR\ConnectionsBundle\EventListener\ImportSourceEventListener'
                ))->addTag(
                    'kernel.event_listener',
                    ['event' => 'ongr.pipeline.import.test.content.source', 'method' => 'onSource']
                )->addMethodCall(
                    'setDoctrineManager',
                    [new Reference('doctrine.orm.default_entity_manager')]
                )->addMethodCall(
                    'setElasticsearchManager',
                    [new Reference('es.manager.default')]
                )->addMethodCall(
                    'setEntityClass',
                    ['TestBundle:Content']
                )->addMethodCall(
                    'setDocumentClass',
                    ['TestBundle:Content']
                ),

                'ongr_connections.pipelines.sync.execute.test.content.source.0' => (new Definition(
                    'ONGR\ConnectionsBundle\EventListener\SyncExecuteSourceEventListener'
                ))->addTag(
                    'kernel.event_listener',
                    ['event' => 'ongr.pipeline.sync.execute.test.content.source', 'method' => 'onSource']
                )->addMethodCall(
                    'setDoctrineManager',
                    [new Reference('doctrine.orm.default_entity_manager')]
                )->addMethodCall(
                    'setElasticsearchManager',
                    [new Reference('es.manager.default')]
                )->addMethodCall(
                    'setSyncStorage',
                    [new Reference('ongr_connections.sync.sync_storage')]
                )->addMethodCall(
                    'setChunkSize',
                    [1]
                )->addMethodCall(
                    'setShopId',
                    [0]
                )->addMethodCall(
                    'setEntityClass',
                    ['TestBundle:Content']
                )->addMethodCall(
                    'setDocumentClass',
                    ['TestBundle:Content']
                )->addMethodCall(
                    'setDocumentType',
                    ['content']
                ),

                'ongr_connections.pipelines.import.test.content.modify.0' => (new Definition(
                    'ONGR\ConnectionsBundle\EventListener\ModifyEventListener'
                ))->addTag(
                    'kernel.event_listener',
                    ['event' => 'ongr.pipeline.import.test.content.modify', 'method' => 'onModify']
                )->addTag(
                    'kernel.event_listener',
                    ['event' => 'ongr.pipeline.sync.execute.test.content.modify', 'method' => 'onModify']
                ),

                'ongr_connections.pipelines.import.test.content.consume.0' => (new Definition(
                    'ONGR\ConnectionsBundle\EventListener\ImportConsumeEventListener'
                ))->addTag(
                    'kernel.event_listener',
                    ['event' => 'ongr.pipeline.import.test.content.consume', 'method' => 'onConsume']
                )->addMethodCall(
                    'setElasticsearchManager',
                    [new Reference('es.manager.default')]
                ),

                'ongr_connections.pipelines.sync.execute.test.content.consume.0' => (new Definition(
                    'ONGR\ConnectionsBundle\EventListener\SyncExecuteConsumeEventListener'
                ))->addTag(
                    'kernel.event_listener',
                    ['event' => 'ongr.pipeline.sync.execute.test.content.consume', 'method' => 'onConsume']
                )->addMethodCall(
                    'setElasticsearchManager',
                    [new Reference('es.manager.default')]
                )->addMethodCall(
                    'setSyncStorage',
                    [new Reference('ongr_connections.sync.sync_storage')]
                )->addMethodCall(
                    'setDocumentClass',
                    ['TestBundle:Content']
                ),

                'ongr_connections.pipelines.import.test.content.finish.0' => (new Definition(
                    'ONGR\ConnectionsBundle\EventListener\ImportFinishEventListener'
                ))->addTag(
                    'kernel.event_listener',
                    ['event' => 'ongr.pipeline.import.test.content.finish', 'method' => 'onFinish']
                )->addTag(
                    'kernel.event_listener',
                    ['event' => 'ongr.pipeline.sync.execute.test.content.finish', 'method' => 'onFinish']
                )->addMethodCall(
                    'setElasticsearchManager',
                    [new Reference('es.manager.default')]
                ),

                'ongr_connections.pipelines.import.test.content.modify.1' => (new Definition(
                    'ONGR\ConnectionsBundle\EventListener\ModifyEventListener'
                ))->addTag(
                    'kernel.event_listener',
                    ['event' => 'ongr.pipeline.import.test.content.modify', 'method' => 'onModify']
                )->addTag(
                    'kernel.event_listener',
                    ['event' => 'ongr.pipeline.sync.execute.test.content.modify', 'method' => 'onModify']
                ),
            ],
        ];

        // Case #2. Test invalid shop.
        $cases[] = [
            'pipelineConfig' => [
                'pipelines' => [
                    'test' => [
                        'shop' => 'invalid',
                        'types' => [
                            'content' => [
                                'entity_class' => 'TestBundle:Content',
                                'document_class' => 'TestBundle:Content',
                            ],
                        ],
                    ],
                ],
            ],
            'expectedDefinitions' => [],
            'expectedException' => 'InvalidArgumentException',
            'expectedExceptionMessage' => 'Non existing shop provided for pipeline test',
        ];

        // Case #3. Test invalid modifier.
        $cases[] = [
            'pipelineConfig' => [
                'pipelines' => [
                    'test' => [
                        'types' => [
                            'content' => [
                                'entity_class' => 'TestBundle:Content',
                                'document_class' => 'TestBundle:Content',
                                'modifiers' => ['ONGR\ConnectionsBundle\EventListener\NonExistingModifyEventListener'],
                            ],
                        ],
                    ],
                ],
            ],
            'expectedDefinitions' => [],
            'expectedException' => 'LogicException',
            'expectedExceptionMessage' => 'Methods could not be extracted from class',
        ];

        return $cases;
    }

    /**
     * Test generated services.
     *
     * @param array  $pipelineConfig
     * @param array  $expectedDefinitions
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     *
     * @dataProvider pipelineGenerationProvider
     */
    public function testPipelineGeneration(
        $pipelineConfig,
        $expectedDefinitions,
        $expectedException = null,
        $expectedExceptionMessage = ''
    ) {
        $containerBuilder = new ContainerBuilder();
        $ONGRConnectionsExtension = new ONGRConnectionsExtension();
        if ($expectedException !== null) {
            $this->setExpectedException($expectedException, $expectedExceptionMessage);
        }
        $ONGRConnectionsExtension->load([$pipelineConfig], $containerBuilder);
        $this->assertEquals(
            $expectedDefinitions,
            $this->getPipelineDefinitions($containerBuilder->getDefinitions())
        );
    }

    /**
     * Returns not default definitions.
     *
     * @param array $definitions
     *
     * @return array
     */
    private function getPipelineDefinitions($definitions)
    {
        foreach (array_keys($definitions) as $key) {
            if (substr($key, 0, 27) !== 'ongr_connections.pipelines.') {
                unset($definitions[$key]);
            }
        }

        return $definitions;
    }
}
