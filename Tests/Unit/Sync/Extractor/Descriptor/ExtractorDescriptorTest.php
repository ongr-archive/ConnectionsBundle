<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Unit\Sync\Extractor\Descriptor;

use ONGR\ConnectionsBundle\Sync\ActionTypes;
use ONGR\ConnectionsBundle\Sync\Extractor\Descriptor\ExtractorDescriptor;
use ReflectionClass;

class ExtractorDescriptorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests setters and getters.
     */
    public function testSetterAndGetter()
    {
        $class = new ReflectionClass('ONGR\ConnectionsBundle\Sync\Extractor\Descriptor\ExtractorDescriptor');
        $defaultJobTypeProperty = $class->getProperty('defaultJobType');
        $defaultJobTypeProperty->setAccessible(true);

        $descriptor = new ExtractorDescriptor();

        $descriptor->setDefaultJobType(ExtractorDescriptor::TYPE_FULL);
        $this->assertEquals(ExtractorDescriptor::TYPE_FULL, $defaultJobTypeProperty->getValue($descriptor));

        $descriptor->setUpdateFields(['field']);
        $this->assertEquals(['field'], $descriptor->getUpdateFields());

        $descriptor->setTable('table');
        $this->assertEquals('table', $descriptor->getTable());

        $descriptor->setTriggerName('triggerName');
        $this->assertEquals('triggerName', $descriptor->getTriggerName());

        $descriptor->setName('descriptorName');
        $this->assertEquals('descriptorName', $descriptor->getName());

        $descriptor->setTriggerType(ActionTypes::CREATE);
        $this->assertEquals('INSERT', $descriptor->getTriggerType());
        $this->assertEquals(ActionTypes::CREATE, $descriptor->getTriggerTypeAlias());

        $this->setExpectedException('\InvalidArgumentException', 'The type MUST be one of:');
        $descriptor->setTriggerType('InvalidType');
    }
}
