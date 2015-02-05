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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Helper base class for adding MySQL parameters to the given definition.
 */
abstract class AbstractExtractionDescriptorPass
{
    /**
     * @param ContainerBuilder $container
     * @param Definition       $definition
     */
    protected function addParameters(ContainerBuilder $container, Definition $definition)
    {
        $definition->addMethodCall(
            'setShops',
            [array_keys($container->getParameter('ongr_connections.shops'))]
        );
        $definition->addMethodCall(
            'setActiveShop',
            [$container->getParameter('ongr_connections.active_shop')]
        );
    }
}
