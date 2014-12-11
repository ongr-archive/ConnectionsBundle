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

use ONGR\ConnectionsBundle\Sync\Panther\Panther;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Loads and manages bundle configuration.
 */
class ONGRConnectionsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('url_invalidator.yml');
        $loader->load('import.yml');
        $loader->load('binlog.yml');
        $loader->load('extractor.yml');
        $loader->load('sync_storage.yml');

        $activeShop = !empty($config['active_shop']) ? $config['active_shop'] : null;
        $container->setParameter('ongr_connections.active_shop', $activeShop);
        $container->setParameter('ongr_connections.shops', $config['shops']);
        $container->setParameter('ongr_connections.sync.jobs_table_name', $config['sync']['jobs_table_name']);

        if ($activeShop !== null && !isset($config['shops'][$activeShop])) {
            throw new \LogicException(
                "Parameter 'ongr_connections.active_shop' must have value one of defined in 'ongr_connections.shops'."
            );
        }

        $doctrineConnection = sprintf('doctrine.dbal.%s_connection', $config['sync']['jobs_connection']);

        $definition = $container->getDefinition('ongr_connections.sync.table_manager');
        $definition->setArguments(
            [
                new Reference($doctrineConnection, ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
                $config['sync']['jobs_table_name'],
                array_keys($config['shops']),
            ]
        );

        // Panther service setup.
        $this->initPanther($container, $config);

        $definition = $container->getDefinition('ongr_connections.mapping_listener');

        $definition->addMethodCall('addReplacement', ['@sync_jobs_table', $config['sync']['jobs_table_name']]);

        $activeShopReplacement = !empty($activeShop) ? "_{$activeShop}" : '';
        $definition->addMethodCall('addReplacement', ['@active_shop', $activeShopReplacement]);
    }

    /**
     * Initializes Panther service.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     *
     * @throws \LogicException
     */
    private function initPanther(ContainerBuilder $container, array $config)
    {
        if (!isset($config['sync']['panther']) || empty($config['sync']['panther'])) {
            throw new \LogicException('Parameter \'ongr_connections.sync.panther\' must be set');
        }

        $availableStorages = array_keys($config['sync']['panther']);
        $pantherStorage = current($availableStorages);
        if (empty($pantherStorage)) {
            throw new \LogicException('Storage for Panther must be set.');
        }

        $pantherStorageConfig = $config['sync']['panther'][$pantherStorage];

        switch ($pantherStorage) {
            case Panther::STORAGE_MYSQL:
                $this->initPantherForMysql($container, $pantherStorageConfig);
                break;
            default:
                throw new \LogicException('Unknown storage for Panther.');
        }
    }

    /**
     * Set-up Panther with MySQL storage.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function initPantherForMysql(ContainerBuilder $container, array $config)
    {
        // Initiate MySQL storage manager.
        $doctrineConnection = sprintf('doctrine.dbal.%s_connection', $config['connection']);
        $definition = $container->getDefinition('ongr_connections.sync.panther.storage_manager.mysql_storage_manager');
        $definition->setArguments(
            [
                new Reference($doctrineConnection, ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
                $config['table_name'],
            ]
        );

        // Initiate Panther and inject storage manager into it.
        $definition = $container->getDefinition('ongr_connections.sync.panther');
        $definition->setArguments(
            [$container->getDefinition('ongr_connections.sync.panther.storage_manager.mysql_storage_manager')]
        );
    }
}
