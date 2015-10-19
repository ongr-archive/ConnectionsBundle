<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Validates and merges configuration from app/config files.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ongr_connections');

        $rootNode
            ->children()
                ->arrayNode('sync')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('managers')
                            ->useAttributeAsKey('manager')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('manager')->end()
                                    ->scalarNode('data_collector')->end()
                                ->end()
                            ->end()
                            ->defaultValue(
                                [
                                    'default' => [
                                        'data_collector' => 'ongr_connections.doctrine_data_collector',
                                    ],
                                ]
                            )
                        ->end()
                        ->arrayNode('sync_storage')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('mysql')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('connection')->defaultValue('default')->end()
                                        ->scalarNode('table_name')->defaultValue('ongr_sync_storage')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('active_shop')->defaultValue('default')->end()
                ->arrayNode('shops')
                    ->info('List of available shops')
                    ->useAttributeAsKey('shop')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('shop_id')->end()
                        ->end()
                    ->end()
                    ->defaultValue(
                        [
                            'default' => [
                                'shop_id' => '0',
                            ],
                        ]
                    )
                ->end()
                ->scalarNode('entity_namespace')
                    ->defaultValue('ONGRConnectionsBundle:')
                    ->info('Namespace/alias for ONGRConnectionsBundle related entities')
                    ->beforeNormalization()
                        ->ifTrue(
                            function ($value) {
                                return strpos($value, '\\') === false;
                            }
                        )
                        ->then(
                            function ($value) {
                                return rtrim($value, ':') . ':';
                            }
                        )
                    ->end()
                    ->beforeNormalization()
                        ->ifTrue(
                            function ($value) {
                                return strpos($value, '\\') !== false;
                            }
                        )
                        ->then(
                            function ($value) {
                                return rtrim($value, '\\') . '\\';
                            }
                        )
                    ->end()
                ->end()
                ->append($this->getPipelinesTree())
            ->end();

        return $treeBuilder;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    private function getPipelinesTree()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('pipelines');

        $rootNode
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('doctrineManager')
                        ->defaultValue('@doctrine.orm.default_entity_manager')
                    ->end()
                    ->scalarNode('elasticsearchManager')
                        ->defaultValue('@es.manager.default')
                    ->end()
                    ->scalarNode('sync_storage')
                        ->defaultValue('@ongr_connections.sync.sync_storage')
                    ->end()
                    ->scalarNode('chunk_size')
                        ->defaultValue(1)
                    ->end()
                    ->scalarNode('shop')
                        ->defaultNull()
                        ->info('Name of shop. Value of null will be replaced with active shop.')
                    ->end()
                    ->scalarNode('diff_provider')
                        ->defaultValue('@ongr_connections.sync.diff_provider.binlog_diff_provider')
                    ->end()
                    ->scalarNode('extractor')
                        ->defaultValue('@ongr_connections.sync.extractor.doctrine_extractor')
                    ->end()
                    ->arrayNode('config')
                       ->prototype('variable')->end()
                    ->end()
                    ->arrayNode('provide_sources')
                        ->prototype('scalar')->end()
                        ->defaultValue(
                            ['ONGR\ConnectionsBundle\EventListener\DataSyncSourceEventListener']
                        )
                    ->end()
                    ->arrayNode('provide_consumers')
                        ->prototype('scalar')->end()
                        ->defaultValue(
                            ['ONGR\ConnectionsBundle\EventListener\DataSyncConsumeEventListener']
                        )
                    ->end()
                    ->arrayNode('import_sources')
                        ->prototype('scalar')->end()
                        ->defaultValue(
                            ['ONGR\ConnectionsBundle\EventListener\ImportSourceEventListener']
                        )
                    ->end()
                    ->arrayNode('import_sources')
                        ->prototype('scalar')->end()
                        ->defaultValue(
                            ['ONGR\ConnectionsBundle\EventListener\ImportSourceEventListener']
                        )
                    ->end()
                    ->arrayNode('sync_sources')
                        ->prototype('array')->end()
                        ->defaultValue(
                            ['ONGR\ConnectionsBundle\EventListener\SyncExecuteSourceEventListener']
                        )
                    ->end()
                    ->arrayNode('modifiers')
                        ->prototype('scalar')->end()
                        ->defaultValue(
                            ['ONGR\ConnectionsBundle\EventListener\ModifyEventListener']
                        )
                    ->end()
                    ->arrayNode('import_consumers')
                        ->prototype('scalar')->end()
                        ->defaultValue(
                            ['ONGR\ConnectionsBundle\EventListener\ImportConsumeEventListener']
                        )
                    ->end()
                    ->arrayNode('sync_consumers')
                        ->prototype('scalar')->end()
                        ->defaultValue(
                            ['ONGR\ConnectionsBundle\EventListener\SyncExecuteConsumeEventListener']
                        )
                    ->end()
                    ->arrayNode('finishers')
                        ->prototype('scalar')->end()
                        ->defaultValue(
                            ['ONGR\ConnectionsBundle\EventListener\ImportFinishEventListener']
                        )
                    ->end()
                    ->arrayNode('types')
                        ->useAttributeAsKey('document_type')
                        ->prototype('array')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity_class')
                                    ->isRequired()
                                ->end()
                                ->scalarNode('document_class')
                                    ->isRequired()
                                ->end()
                                ->arrayNode('config')
                                    ->prototype('variable')->end()
                                ->end()
                                ->arrayNode('import_sources')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('sync_sources')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('modifiers')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('import_consumers')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('sync_consumers')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('finishers')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }
}
