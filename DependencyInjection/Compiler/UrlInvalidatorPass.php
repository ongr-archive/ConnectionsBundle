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
 * Class UrlInvalidatorPass.
 */
class UrlInvalidatorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ongr_connections.url_invalidator_service')) {
            return;
        }
        $readerDefinition = $container->getDefinition('ongr_connections.url_invalidator_service');

        foreach (array_keys($container->findTaggedServiceIds('ongr_connections.document_url_collector')) as $id) {
            $readerDefinition->addMethodCall('addUrlCollector', [new Reference($id)]);
        }
    }
}
