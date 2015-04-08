<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle;

use ONGR\ConnectionsBundle\DependencyInjection\Compiler\ExtractionDescriptorPass;
use ONGR\ConnectionsBundle\DependencyInjection\Compiler\ModifierClassPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * ONGR Connections Bundle.
 */
class ONGRConnectionsBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ExtractionDescriptorPass());
        $container->addCompilerPass(new ModifierClassPass());
    }
}
