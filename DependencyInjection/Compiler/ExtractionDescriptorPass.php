<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds services tagged as sql relation to descriptors collection.
 */
class ExtractionDescriptorPass extends AbstractExtractionDescriptorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ongr_connections.sync.extraction_collection')) {
            return;
        }
        $collectionDefinition = $container->getDefinition('ongr_connections.sync.extraction_collection');
        foreach ($container->findTaggedServiceIds('ongr_connections.extraction_descriptor') as $id => $tags) {
            $definition = $container->getDefinition($id);
            $definition->addMethodCall('setName', [$id]);
            $this->addParameters($container, $definition);
            $collectionDefinition->addMethodCall('addDescriptor', [new Reference($id)]);
        }
    }
}
