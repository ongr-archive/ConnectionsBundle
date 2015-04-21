<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use ONGR\ConnectionsBundle\EventListener\LoadClassMetadataListener;

/**
 * Test for LoadClassMetadataListener.
 */
class LoadClassMetadataListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for testMetadataProcessing().
     *
     * @return array
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function getTestMetadataProcessingData()
    {
        $out = [];

        /** @var ObjectManager $entityManager Common data for most test cases. */
        $entityManager = $this->getMock('Doctrine\\ORM\\EntityManagerInterface');

        $baseMapping = [
            'fieldName' => 'fieldNameValue',
        ];

        $replacements = [
            '@placeholder' => '_1',
        ];

        // Case 0: no replacements, no mapping.
        $classMetadataInfo = new ClassMetadataInfo('someEntity');

        $input = new LoadClassMetadataEventArgs($classMetadataInfo, $entityManager);
        $output = new LoadClassMetadataEventArgs($classMetadataInfo, $entityManager);

        $out[] = [[], $input, $output];

        // Case 1: no replacements, default mappings.
        $inputClassMetadataInfo = new ClassMetadataInfo('someEntity');
        $inputClassMetadataInfo->mapField($baseMapping);
        $input = new LoadClassMetadataEventArgs($inputClassMetadataInfo, $entityManager);

        $outputClassMetadataInfo = new ClassMetadataInfo('someEntity');
        $outputClassMetadataInfo->mapField($baseMapping);
        $output = new LoadClassMetadataEventArgs($outputClassMetadataInfo, $entityManager);

        $out[] = [[], $input, $output];

        // Case 2: no replacements.
        $inputClassMetadataInfo = new ClassMetadataInfo('someEntity');
        $inputClassMetadataInfo->mapField(
            array_merge(
                $baseMapping,
                [
                    'key' => 'value',
                ]
            )
        );
        $input = new LoadClassMetadataEventArgs($inputClassMetadataInfo, $entityManager);

        $outputClassMetadataInfo = new ClassMetadataInfo('someEntity');
        $outputClassMetadataInfo->mapField(
            array_merge(
                $baseMapping,
                [
                    'key' => 'value',
                ]
            )
        );
        $output = new LoadClassMetadataEventArgs($outputClassMetadataInfo, $entityManager);

        $out[] = [[], $input, $output];

        // Case 3: irrelevant replacements.
        $inputClassMetadataInfo = new ClassMetadataInfo('someEntity');
        $inputClassMetadataInfo->mapField(
            array_merge(
                $baseMapping,
                [
                    'key' => 'value',
                ]
            )
        );
        $input = new LoadClassMetadataEventArgs($inputClassMetadataInfo, $entityManager);

        $outputClassMetadataInfo = new ClassMetadataInfo('someEntity');
        $outputClassMetadataInfo->mapField(
            array_merge(
                $baseMapping,
                [
                    'key' => 'value',
                ]
            )
        );
        $output = new LoadClassMetadataEventArgs($outputClassMetadataInfo, $entityManager);

        $out[] = [$replacements, $input, $output];

        // Case 4: replacements provided.
        $inputClassMetadataInfo = new ClassMetadataInfo('someEntity');
        $inputClassMetadataInfo->mapField(
            array_merge(
                $baseMapping,
                [
                    'key' => 'value@placeholder',
                ]
            )
        );
        $input = new LoadClassMetadataEventArgs($inputClassMetadataInfo, $entityManager);

        $outputClassMetadataInfo = new ClassMetadataInfo('someEntity');
        $outputClassMetadataInfo->mapField(
            array_merge(
                $baseMapping,
                [
                    'key' => 'value_1',
                ]
            )
        );
        $output = new LoadClassMetadataEventArgs($outputClassMetadataInfo, $entityManager);

        $out[] = [$replacements, $input, $output];

        // Case 5: nested annotations.
        $inputClassMetadataInfo = new ClassMetadataInfo('someEntity');
        $inputClassMetadataInfo->mapField(
            array_merge(
                $baseMapping,
                [
                    'key' => [
                        'internalKey' => ['value@placeholder'],
                    ],
                ]
            )
        );
        $input = new LoadClassMetadataEventArgs($inputClassMetadataInfo, $entityManager);

        $outputClassMetadataInfo = new ClassMetadataInfo('someEntity');
        $outputClassMetadataInfo->mapField(
            array_merge(
                $baseMapping,
                [
                    'key' => [
                        'internalKey' => ['value@placeholder'],
                    ],
                ]
            )
        );
        $output = new LoadClassMetadataEventArgs($outputClassMetadataInfo, $entityManager);

        $out[] = [$replacements, $input, $output];

        // Case 6: replacing table name.
        $inputClassMetadataInfo = new ClassMetadataInfo('someEntity');
        $inputClassMetadataInfo->mapField($baseMapping);
        $inputClassMetadataInfo->setPrimaryTable(
            [
                'name' => 'my_table@placeholder',
            ]
        );
        $input = new LoadClassMetadataEventArgs($inputClassMetadataInfo, $entityManager);

        $outputClassMetadataInfo = new ClassMetadataInfo('someEntity');
        $outputClassMetadataInfo->mapField($baseMapping);
        $outputClassMetadataInfo->setPrimaryTable(
            [
                'name' => 'my_table_1',
            ]
        );
        $output = new LoadClassMetadataEventArgs($outputClassMetadataInfo, $entityManager);

        $out[] = [$replacements, $input, $output];

        // Case 7: one-to-one mapping.
        $replacements = [
            '@placeholder' => '_1',
        ];

        $inputClassMetadataInfo = new ClassMetadataInfo('someEntity');
        $inputClassMetadataInfo->setPrimaryTable(
            [
                'name' => 'my_table',
            ]
        );
        $inputClassMetadataInfo->mapOneToOne(
            array_merge(
                $baseMapping,
                [
                    'targetEntity' => 'whatever',
                    'joinColumns' => [
                        [
                            'name' => 'someName',
                            'referencedColumnName' => 'FK@placeholder',
                        ],
                    ],
                ]
            )
        );
        $input = new LoadClassMetadataEventArgs($inputClassMetadataInfo, $entityManager);

        $outputClassMetadataInfo = new ClassMetadataInfo('someEntity');
        $outputClassMetadataInfo->setPrimaryTable(
            [
                'name' => 'my_table',
            ]
        );
        $outputClassMetadataInfo->mapOneToOne(
            array_merge(
                $baseMapping,
                [
                    'targetEntity' => 'whatever',
                    'joinColumns' => [
                        [
                            'name' => 'someName',
                            'referencedColumnName' => 'FK_1',
                        ],
                    ],
                    'joinTableColumns' => null,
                    'relationToSourceKeyColumns' => null,
                    'relationToTargetKeyColumns' => null,
                ]
            )
        );
        $output = new LoadClassMetadataEventArgs($outputClassMetadataInfo, $entityManager);

        $out[] = [$replacements, $input, $output];

        return $out;
    }

    /**
     * Function testMetadataProcessing.
     *
     * @param array                      $replacements
     * @param LoadClassMetadataEventArgs $input
     * @param LoadClassMetadataEventArgs $expectedOutput
     *
     * @dataProvider    getTestMetadataProcessingData()
     */
    public function testMetadataProcessing($replacements, $input, $expectedOutput)
    {
        $service = new LoadClassMetadataListener($replacements);

        $service->loadClassMetadata($input);

        $this->assertEquals(
            $expectedOutput,
            $input
        );
    }

    /**
     * Test replacements using addReplacement().
     */
    public function testMetadataProcessing2()
    {
        /** @var ObjectManager $entityManager */
        $entityManager = $this->getMock('Doctrine\\ORM\\EntityManagerInterface');

        $inputClassMetadataInfo = new ClassMetadataInfo('someEntity');
        $inputClassMetadataInfo->mapField(['fieldName' => 'any_field', 'key' => 'any_field@placeholder']);
        $inputClassMetadataInfo->setDiscriminatorMap(['@discriminatorPlaceholder' => 'StdClass']);
        $inputClassMetadataInfo->setDiscriminatorMap(['partial@discriminatorPlaceholder' => 'StdClass']);
        $input = new LoadClassMetadataEventArgs($inputClassMetadataInfo, $entityManager);

        $service = new LoadClassMetadataListener();
        $service->loadClassMetadata($input);
        $service->addReplacement('@placeholder', '_1');
        $service->addReplacement('@discriminatorPlaceholder', '_bestShop');
        $service->loadClassMetadata($input);

        $outputClassMetadataInfo = new ClassMetadataInfo('someEntity');
        $outputClassMetadataInfo->mapField(['fieldName' => 'any_field', 'key' => 'any_field_1']);
        $outputClassMetadataInfo->setDiscriminatorMap(['_bestShop' => 'StdClass']);
        $outputClassMetadataInfo->setDiscriminatorMap(['partial_bestShop' => 'StdClass']);

        $this->assertEquals($outputClassMetadataInfo, $input->getClassMetadata());
    }
}
