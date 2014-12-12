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
                        ->scalarNode('jobs_table_name')->defaultValue('ongr_sync_jobs')->end()
                        ->scalarNode('jobs_connection')->defaultValue('default')->end()
                        ->arrayNode('managers')
                            ->useAttributeAsKey('manager')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('manager')->end()
                                    ->scalarNode('job_manager')->end()
                                    ->scalarNode('data_collector')->end()
                                ->end()
                            ->end()
                            ->defaultValue(
                                [
                                    'default' => [
                                        'job_manager' => 'ongr_connections.sync.job_manager',
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
                                        ->scalarNode('table_name')->defaultValue('ongr_sync_storage_storage')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('active_shop')->end()
                ->arrayNode('shops')
                    ->info('List of available shops')
                    ->useAttributeAsKey('shop_id')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('shop_id')->end()
                        ->end()
                    ->end()
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
            ->end();

        return $treeBuilder;
    }
}
