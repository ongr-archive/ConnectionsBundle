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
class ExtractorDescriptorPass extends AbstractExtractorDescriptorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ongr_connections.sync.extraction_collection')) {
            return;
        }
        $triggersManagerDefinition = $container->getDefinition('ongr_connections.sync.extraction_collection');
        foreach ($container->findTaggedServiceIds('ongr_connections.extractor_descriptor') as $id => $tags) {
            $definition = $container->getDefinition($id);
            $definition->addMethodCall('setName', [$id]);
            $this->addParameters($container, $definition);
            $triggersManagerDefinition->addMethodCall('addDescriptor', [new Reference($id)]);
        }
    }
}
