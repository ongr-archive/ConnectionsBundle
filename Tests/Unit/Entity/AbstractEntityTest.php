<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Unit\Entity;

/**
 * Abstract entity test for setters and getters.
 */
abstract class AbstractEntityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Returns list of fields to test. Works as data provider.
     *
     * @return array
     */
    abstract public function getFieldsData();

    /**
     * Returns entity class name.
     *
     * @return string
     */
    abstract public function getClassName();

    /**
     * Returns list of fields that should not be checked for tests.
     *
     * @return array
     */
    protected function getIgnoredFields()
    {
        return [];
    }

    /**
     * Tests field setter and getter.
     *
     * @param string      $field
     * @param null|string $type
     * @param null|string $addMethod
     * @param null|string $removeMethod
     *
     * @throws \Exception When unknown field type given
     *
     * @dataProvider getFieldsData()
     */
    public function testSetterGetter($field, $type = null, $addMethod = null, $removeMethod = null)
    {
        $objectClass = $this->getClassName();

        $setter = 'set' . ucfirst($field);
        $getter = 'get' . ucfirst($field);

        if ($type === 'boolean') {
            $getter = 'is' . ucfirst($field);
        }

        $stub = $this->getMockForAbstractClass($objectClass);

        if ($addMethod) {
            $this->assertTrue(method_exists($stub, $addMethod), "Method ${addMethod}() not found!");
            $this->assertTrue(method_exists($stub, $getter), "Method ${getter}() not found!");
            $this->assertTrue(method_exists($stub, $removeMethod), "Method ${removeMethod}() not found!");
        } else {
            $this->assertTrue(method_exists($stub, $setter), "Method ${getter}() not found!");
            $this->assertTrue(method_exists($stub, $getter), "Method ${setter}() not found!");
        }

        if ($type === null || $type == 'boolean') {
            $rand = rand(0, 9999);
            $stub->$setter($rand);
            $this->assertEquals($rand, $stub->$getter());
        } elseif ($type == '\DateTime') {
            $dateTime = new \DateTime();
            $stub->$setter($dateTime);
            $this->assertEquals($dateTime, $stub->$getter());
        } elseif (class_exists($type)) {
            $childObject = $this->getMockForAbstractClass($type);
            $hash = spl_object_hash($childObject);

            if ($addMethod) {
                $stub->$addMethod($childObject);

                foreach ($stub->$getter() as $collectionObject) {
                    $this->assertEquals($hash, spl_object_hash($collectionObject));
                }

                $stub->$removeMethod($childObject);
                $this->assertEquals(0, count($stub->$getter()));
            } else {
                $stub->$setter($childObject);
                $this->assertEquals($hash, spl_object_hash($stub->$getter()));
            }
        } else {
            throw new \Exception("Unknown field type '{$type}'.");
        }
    }

    /**
     * Tests if all entity fields are registered.
     */
    public function testAllEntityFieldsRegistered()
    {
        $reflect = new \ReflectionClass($this->getClassName());
        $properties = $reflect->getProperties();

        $fields = [];

        /** @var \ReflectionProperty $property */
        foreach ($properties as $property) {
            $fields[] = $property->getName();
        }

        $registeredFields = [];

        foreach ($this->getFieldsData() as $data) {
            $registeredFields[] = $data[0];
        }

        $diff = array_diff($fields, $registeredFields, $this->getIgnoredFields());

        if (count($diff) !== 0) {
            $this->fail(
                sprintf(
                    'All entity fields must be registered in test. Please check field(s) "%s".',
                    implode('", "', $diff)
                )
            );
        }
    }
}
