<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestsUnit\inc\SugarFields\Fields\Collection;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \SugarFieldCollection
 */
class SugarFieldCollectionTest extends TestCase
{
    /**
     * @covers ::processAdditionalAcls
     * @dataProvider providerProcessAdditionalAcls
     * @param string $fieldName Name of the relevant field.
     * @param array $forbiddenLinks List of ACL-restricted links.
     * @param array $data Record data, including a relevant collection field.
     * @param array $fieldDef Field definitions.
     * @param array $expectedResult Expected record data after ACL pruning.
     */
    public function testProcessAdditionalAcls(
        string $fieldName,
        array  $forbiddenLinks,
        array  $data,
        array  $fieldDef,
        array  $expectedResult
    ) {

        $bean = $this->createMock(\SugarBean::class);
        $api = $this->createMock(\ServiceBase::class);

        $sfc = $this->createPartialMock(\SugarFieldCollection::class, ['getForbiddenLinks']);
        $sfc->expects($this->once())
            ->method('getForbiddenLinks')
            ->willReturn($forbiddenLinks);

        $sfc->processAdditionalAcls($data, $bean, $fieldName, 'read', $fieldDef, $api);

        $this->assertEquals(
            $expectedResult,
            $data,
            'SugarFieldCollection did not properly remove ACL-forbidden link data'
        );
    }

    public function providerProcessAdditionalAcls(): array
    {
        $generalTestCase = [
            'programs', // field name
            ['top-secret-programs'], // link names
            // data
            [
                'innocuous-data' => '777',
                'programs' => [
                    'records' => [
                        ['id' => 'id1', 'name' => 'Top Secret Program #1', '_link' => 'top-secret-programs'],
                        ['id' => 'id2', 'name' => 'Top Secret Program #2', '_link' => 'top-secret-programs'],
                        ['id' => 'id3', 'name' => 'Unclassified Program', '_link' => 'unclassified-programs'],
                    ],
                    'next_offset' => [
                        'top-secret-programs' => -1,
                        'unclassified-programs' => -1,
                    ],
                ],
            ],
            // fieldDef
            [
                'name' => 'programs',
                'vname' => 'LBL_PROGRAMS',
                'type' => 'collection',
                'links' => ['unclassified-programs', 'top-secret-programs'],
                'source' => 'non-db',
                'module' => 'Programs',
            ],
            // expected results
            [
                'innocuous-data' => '777',
                'programs' => [
                    'records' => [
                        ['id' => 'id3', 'name' => 'Unclassified Program', '_link' => 'unclassified-programs'],
                    ],
                    'next_offset' => ['unclassified-programs' => -1],
                ],
            ],
        ];

        $noRelevantLinksTestCase = [
            'unprotected-collection', // field name
            ['secret-collection-link'],  // link names
            // data
            [
                'unprotected-collection' => [
                    'records' => [
                        ['id' => 'id4', 'name' => 'An unexciting record', '_link' => 'unprotected-collection-link'],
                    ],
                    'next_offset' => ['unprotected-collection-link' => -1],
                ],
            ],
            // fieldDef
            [
                'name' => 'unprotected-collection',
                'vname' => 'LBL_UNPROTECTED_COLLECTION',
                'type' => 'collection',
                'links' => ['unprotected-collection-link'],
                'source' => 'non-db',
                'module' => 'MyModule',
            ],
            // expected results
            [
                'unprotected-collection' => [
                    'records' => [
                        ['id' => 'id4', 'name' => 'An unexciting record', '_link' => 'unprotected-collection-link'],
                    ],
                    'next_offset' => ['unprotected-collection-link' => -1],
                ],
            ],
        ];

        $unrestrictedTestCase = [
            'any-old-collection', // field name
            [], // link names - no restrictions at all
            // data
            [
                'any-old-collection' => [
                    'records' => [
                        ['id' => 'id5', 'name' => 'Just Another Record', '_link' => 'my-collection-link'],
                    ],
                    'next_offset' => ['my-collection-link' => -1],
                ],
            ],
            // fieldDef
            [
                'name' => 'any-old-collection',
                'vname' => 'LBL_ANY_OLD_COLLECTION',
                'type' => 'collection',
                'links' => ['my-collection-link'],
                'source' => 'non-db',
                'module' => 'MyModule',
            ],
            // expected results
            [
                'any-old-collection' => [
                    'records' => [
                        ['id' => 'id5', 'name' => 'Just Another Record', '_link' => 'my-collection-link'],
                    ],
                    'next_offset' => ['my-collection-link' => -1],
                ],
            ],
        ];

        return [
            $generalTestCase,
            $noRelevantLinksTestCase,
            $unrestrictedTestCase,
        ];
    }
}
