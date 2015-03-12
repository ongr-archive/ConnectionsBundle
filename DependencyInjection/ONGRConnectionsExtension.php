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

use LogicException;
use ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorage;
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
        $loader->load('pair_storage.yml');
        $loader->load('binlog.yml');
        $loader->load('extractor.yml');
        $loader->load('sync_storage.yml');
        $loader->load('extractor_relations.yml');
        $loader->load('crawler.yml');

        $this->initShops($container, $config);
        $this->initSyncStorage($container, $config);
    }

    /**
     * Set up shops.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     *
     * @throws LogicException
     */
    private function initShops(ContainerBuilder $container, array $config)
    {
        $activeShop = !empty($config['active_shop']) ? $config['active_shop'] : null;
        if ($activeShop !== null && !isset($config['shops'][$activeShop])) {
            throw new LogicException(
                "Parameter 'ongr_connections.active_shop' must be set to one" .
                "of the values defined in 'ongr_connections.shops'."
            );
        }

        $container->setParameter('ongr_connections.active_shop', $activeShop);
        $container->setParameter('ongr_connections.shops', $config['shops']);
    }

    /**
     * Initializes SyncStorage service.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     *
     * @throws LogicException
     */
    private function initSyncStorage(ContainerBuilder $container, array $config)
    {
        $availableStorages = array_keys($config['sync']['sync_storage']);
        $syncStorageStorage = current($availableStorages);
        if (empty($syncStorageStorage)) {
            throw new LogicException('Data synchronization storage must be set.');
        }

        $syncStorageStorageConfig = $config['sync']['sync_storage'][$syncStorageStorage];

        switch ($syncStorageStorage) {
            case SyncStorage::STORAGE_MYSQL:
                $this->initSyncStorageForMysql($container, $syncStorageStorageConfig);
                break;
            default:
                throw new LogicException("Unknown storage is set: {$syncStorageStorage}");
        }
    }

    /**
     * Set up Sync. storage with MySQL storage.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function initSyncStorageForMysql(ContainerBuilder $container, array $config)
    {
        // Initiate MySQL storage manager.
        $doctrineConnection = sprintf('doctrine.dbal.%s_connection', $config['connection']);
        $definition = $container->getDefinition(
            'ongr_connections.sync.storage_manager.mysql_storage_manager'
        );
        $definition->setArguments(
            [
                new Reference($doctrineConnection, ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
                $config['table_name'],
            ]
        );

        $definition->addMethodCall('setContainer', [new Reference('service_container')]);

        // Initiate SyncStorage and inject storage manager into it.
        $container->getDefinition('ongr_connections.sync.sync_storage')->setArguments(
            [$definition]
        );
    }
}
