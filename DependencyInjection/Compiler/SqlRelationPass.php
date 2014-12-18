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
 * Adds services tagged as sql relation to relations collection.
 */
class SqlRelationPass extends AbstractMySqlPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ongr_connections.sync.relations_collection')) {
            return;
        }
        $triggersManagerDefinition = $container->getDefinition('ongr_connections.sync.relations_collection');
        foreach ($container->findTaggedServiceIds('ongr_connections.sql_relation') as $id => $tags) {
            $definition = $container->getDefinition($id);
            $this->addParameters($container, $definition);
            $triggersManagerDefinition->addMethodCall('addRelation', [new Reference($id)]);
        }
    }
}
