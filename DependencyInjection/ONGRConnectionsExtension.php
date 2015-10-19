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
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;
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
        $this->createPipelines($container, $config);
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
                "Parameter 'ongr_connections.active_shop' must be set to one"
                . "of the values defined in 'ongr_connections.shops'."
            );
        }

        $container->setParameter('ongr_connections.active_shop', $activeShop);
        $container->setParameter('ongr_connections.shops', $config['shops']);

        $container->setDefinition(
            'ongr_connections.shop_service',
            new Definition(
                'ONGR\ConnectionsBundle\Service\ShopService',
                [
                    $activeShop,
                    $config['shops'],
                ]
            )
        );
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

    /**
     * Creates sync, import and provide pipeline services.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function createPipelines(ContainerBuilder $container, array $config)
    {
        foreach ($config['pipelines'] as $pipelineName => $pipelineConfig) {
            if (!isset($pipelineConfig['shop'])) {
                $pipelineConfig['shop'] = $container->getParameter('ongr_connections.active_shop');
            }
            $serviceConfig = $this->prepareServiceConfigs($container, $pipelineConfig, $pipelineName);
            $this->createServices(
                $container,
                $pipelineConfig['provide_sources'],
                $serviceConfig,
                "data_sync.{$pipelineName}.source",
                'onSource'
            );
            $this->createServices(
                $container,
                $pipelineConfig['provide_consumers'],
                $serviceConfig,
                "data_sync.{$pipelineName}.consume",
                'onConsume'
            );
            foreach ($pipelineConfig['types'] as $type => $typeConfig) {
                $typeServiceConfig = $this->prepareTypeServiceConfigs($serviceConfig, $typeConfig, $type);
                $serviceList = $this->getServiceList($pipelineName, $type);
                foreach ($serviceList as $name => $service) {
                    $this->createServices(
                        $container,
                        array_merge($pipelineConfig[$name], $typeConfig[$name]),
                        $typeServiceConfig,
                        $service['tag'],
                        $service['method']
                    );
                }
            }
        }
    }

    /**
     * Retrieves shop id by shop name.
     *
     * @param ContainerBuilder $container
     * @param string           $shop
     * @param string           $name
     *
     * @return mixed
     */
    protected function getShopId(ContainerBuilder $container, $shop, $name)
    {
        $shops = $container->getParameter('ongr_connections.shops');
        if (!isset($shops[$shop])) {
            throw new \InvalidArgumentException('Non existing shop provided for pipeline ' . $name);
        }

        return $shops[$shop]['shop_id'];
    }

    /**
     * Creates service definitions.
     *
     * @param ContainerBuilder $container
     * @param string[]         $classes
     * @param array            $config
     * @param string|string[]  $tag
     * @param string           $method
     */
    protected function createServices(ContainerBuilder $container, $classes, $config, $tag, $method)
    {
        if (!is_array($tag)) {
            $tag = [$tag];
        }

        foreach ($classes as $class) {
            $methods = $this->getMethods($class);
            $definition = new Definition($class);
            $this->setProperties($definition, $config, $methods);
            $this->setTags($definition, $tag, $method);
            $container->setDefinition($this->getServiceName($tag[0]), $definition);
        }
    }

    /**
     * @param Definition $definition
     * @param string     $property
     * @param mixed      $value
     * @param array      $methods
     */
    protected function setProperty(Definition $definition, $property, $value, array $methods)
    {
        $setter = 'set' . ContainerBuilder::camelize($property);
        if (in_array($setter, $methods)) {
            if (is_string($value)) {
                if (strpos($value, '@=') === 0) {
                    $value = new Expression(substr($value, 2));
                } elseif (strpos($value, '@') === 0) {
                    $value = new Reference(substr($value, 1));
                }
            }

            $definition->addMethodCall($setter, [$value]);
        }
    }

    /**
     * @param string|object $class
     *
     * @return array
     */
    protected function getMethods($class)
    {
        $methods = get_class_methods($class);
        if ($methods === null) {
            throw new \LogicException("Methods could not be extracted from class '{$class}'");
        }

        return $methods;
    }

    /**
     * @param Definition $definition
     * @param array      $config
     * @param array      $methods
     */
    protected function setProperties(Definition $definition, $config, array $methods)
    {
        foreach ($config as $property => $value) {
            $this->setProperty($definition, $property, $value, $methods);
        }
    }

    /**
     * @param Definition $definition
     * @param array      $tag
     * @param string     $method
     */
    protected function setTags(Definition $definition, array $tag, $method)
    {
        foreach ($tag as $tagName) {
            $definition->addTag(
                'kernel.event_listener',
                [
                    'event' => 'ongr.pipeline.' . $tagName,
                    'method' => $method,
                ]
            );
        }
    }

    /**
     * @param string $tag
     *
     * @return string
     */
    protected function getServiceName($tag)
    {
        static $counts = [];
        if (isset($counts[$tag])) {
            $counts[$tag]++;
        } else {
            $counts[$tag] = 0;
        }

        return 'ongr_connections.pipelines.' . $tag . '.' . $counts[$tag];
    }

    /**
     * Merges and parses configs.
     *
     * @param ContainerBuilder $container
     * @param array            $pipelineConfig
     * @param string           $pipelineName
     *
     * @return array
     */
    protected function prepareServiceConfigs(ContainerBuilder $container, $pipelineConfig, $pipelineName)
    {
        return array_merge(
            $pipelineConfig['config'],
            [
                'doctrineManager' => $pipelineConfig['doctrineManager'],
                'elasticsearchManager' => $pipelineConfig['elasticsearchManager'],
                'sync_storage' => $pipelineConfig['sync_storage'],
                'diff_provider' => $pipelineConfig['diff_provider'],
                'extractor' => $pipelineConfig['extractor'],
                'chunk_size' => $pipelineConfig['chunk_size'],
                'shop' => $pipelineConfig['shop'],
                'shop_id' => $this->getShopId($container, $pipelineConfig['shop'], $pipelineName),
            ]
        );
    }

    /**
     * Merges global configs with type config.
     *
     * @param array  $serviceConfig
     * @param array  $typeConfig
     * @param string $type
     *
     * @return array
     */
    protected function prepareTypeServiceConfigs($serviceConfig, $typeConfig, $type)
    {
        return array_merge(
            $serviceConfig,
            $typeConfig['config'],
            [
                'entity_class' => $typeConfig['entity_class'],
                'document_class' => $typeConfig['document_class'],
                'document_type' => $type,
            ]
        );
    }

    /**
     * @param string $pipelineName
     * @param string $type
     *
     * @return array
     */
    protected function getServiceList($pipelineName, $type)
    {
        return [
            'import_sources' => [
                'tag' => "import.{$pipelineName}.{$type}.source",
                'method' => 'onSource',
            ],
            'sync_sources' => [
                'tag' => "sync.execute.{$pipelineName}.{$type}.source",
                'method' => 'onSource',
            ],
            'modifiers' => [
                'tag' => [
                    "import.{$pipelineName}.{$type}.modify",
                    "sync.execute.{$pipelineName}.{$type}.modify",
                ],
                'method' => 'onModify',
            ],
            'import_consumers' => [
                'tag' => "import.{$pipelineName}.{$type}.consume",
                'method' => 'onConsume',
            ],
            'sync_consumers' => [
                'tag' => "sync.execute.{$pipelineName}.{$type}.consume",
                'method' => 'onConsume',
            ],
            'finishers' => [
                'tag' => [
                    "import.{$pipelineName}.{$type}.finish",
                    "sync.execute.{$pipelineName}.{$type}.finish",
                ],
                'method' => 'onFinish',
            ],
        ];
    }
}
