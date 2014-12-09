<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\DependencyInjection;

use ONGR\ConnectionsBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

/**
 * Test for Configuration.
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Returns default configuration for bundle.
     *
     * @return array
     */
    protected function getDefaultConfig()
    {
        return [
            'entity_namespace' => 'Acme\DemoBundle\\',
        ];
    }

    /**
     * Tests if expected configuration structure works well.
     */
    public function testConfiguration()
    {
        $configs = $this->getDefaultConfig();

        $processor = new Processor();
        $processedConfig = $processor->processConfiguration(new Configuration(), [$configs]);

        $this->assertEquals($configs['entity_namespace'], $processedConfig['entity_namespace']);
    }

    /**
     * Tests if normalization works well.
     */
    public function testConfigurationEntityNamespaceNormalization()
    {
        $configs = $this->getDefaultConfig();
        $configs['entity_namespace'] = 'AcmeDemoBundle';

        $processor = new Processor();
        $processedConfig = $processor->processConfiguration(new Configuration(), [$configs]);

        $this->assertEquals('AcmeDemoBundle:', $processedConfig['entity_namespace']);
    }

    /**
     * Tests if normalization works well.
     */
    public function testConfigurationEntityNamespaceNormalization2()
    {
        $configs = $this->getDefaultConfig();
        $configs['entity_namespace'] = 'Acme\DemoBundle';

        $processor = new Processor();
        $processedConfig = $processor->processConfiguration(new Configuration(), [$configs]);

        $this->assertEquals('Acme\DemoBundle\\', $processedConfig['entity_namespace']);
    }
}
