<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\DependencyInjection\Compiler;

use ONGR\ConnectionsBundle\DependencyInjection\Compiler\UrlInvalidatorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Test for UrlInvalidatorPass.
 */
class UrlInvalidatorPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Do not do anything width definition if it not exists.
     */
    public function testSkipProcess()
    {
        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->getMock(
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            ['hasDefinition', 'getDefinition']
        );

        $container
            ->expects($this->any())
            ->method('hasDefinition')
            ->with('ongr_connections.url_invalidator_service')
            ->will($this->returnValue(false));

        $container
            ->expects($this->never())
            ->method('getDefinition');

        $pass = new UrlInvalidatorPass();
        $pass->process($container);
    }

    /**
     * Add collectors if definition exist.
     */
    public function testProcess()
    {
        $taggedServices = [
            'bundle_1.tagged_service_1' => [],
            'bundle_2.tagged_service_2' => [],
        ];
        // Set up definition requirements.
        $definition = $this->getMock(
            'Symfony\Component\DependencyInjection\Definition',
            ['addMethodCall']
        );
        $definition
            ->expects($this->at(0))
            ->method('addMethodCall')
            ->with('addUrlCollector', [new Reference('bundle_1.tagged_service_1')]);
        $definition
            ->expects($this->at(1))
            ->method('addMethodCall')
            ->with('addUrlCollector', [new Reference('bundle_2.tagged_service_2')]);

        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->getMock(
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            ['hasDefinition', 'getDefinition', 'findTaggedServiceIds']
        );
        $container
            ->expects($this->any())
            ->method('hasDefinition')
            ->with('ongr_connections.url_invalidator_service')
            ->will($this->returnValue(true));
        $container
            ->expects($this->any())
            ->method('getDefinition')
            ->with('ongr_connections.url_invalidator_service')
            ->will($this->returnValue($definition));
        $container
            ->expects($this->any())
            ->method('findTaggedServiceIds')
            ->with('ongr_connections.document_url_collector')
            ->will($this->returnValue($taggedServices));

        $pass = new UrlInvalidatorPass();
        $pass->process($container);
    }
}
