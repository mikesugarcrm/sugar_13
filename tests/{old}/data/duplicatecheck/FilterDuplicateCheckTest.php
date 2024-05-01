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

use PHPUnit\Framework\TestCase;

class FilterDuplicateCheckTest extends TestCase
{
    private $metadata;

    protected function setUp(): void
    {
        $this->metadata = [
            'filter_template' => [
                [
                    '$and' => [
                        [
                            '$or' => [
                                [
                                    'status' => [
                                        '$not_equals' => 'Converted',
                                    ],
                                ],
                                [
                                    'status' => [
                                        '$is_null' => '',
                                    ],
                                ],
                            ],
                        ],
                        [
                            '$or' => [
                                [
                                    '$and' => [
                                        [
                                            'first_name' => [
                                                '$starts' => '$first_name',
                                            ],
                                        ],
                                        [
                                            'last_name' => [
                                                '$starts' => '$last_name',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'account_name' => [
                                '$equals' => '$account_name',
                            ],
                        ],
                    ],
                ],
            ],
            'ranking_fields' => [
                [
                    'in_field_name' => 'last_name',
                    'dupe_field_name' => 'last_name',
                ],
                [
                    'in_field_name' => 'first_name',
                    'dupe_field_name' => 'first_name',
                ],
            ],
        ];
    }

    /**
     * @group duplicatecheck
     */
    public function testFindDuplicates_NoBeanId_AddFilterForEditsIsNotCalled()
    {
        $bean = $this->createMock(Lead::class);
        $bean->expects(self::any())
            ->method('ACLFieldAccess')
            ->will(self::returnValue(true));

        $filterDuplicateCheckMock = $this->getMockBuilder(FilterDuplicateCheck::class)
            ->setMethods([
                'buildDupeCheckFilter',
                'addFilterForEdits',
                'callFilterApi',
                'rankAndSortDuplicates',
            ])->setConstructorArgs([
                $bean,
                $this->metadata,
            ])->getMock();

        $filterDuplicateCheckMock->expects(self::once())
            ->method('buildDupeCheckFilter')
            ->will(self::returnValue(true));

        // addFilterForEdits should never be called if the bean has no id
        $filterDuplicateCheckMock->expects(self::never())
            ->method('addFilterForEdits');

        $filterDuplicateCheckMock->expects(self::once())
            ->method('callFilterApi')
            ->will(self::returnValue(true));

        $filterDuplicateCheckMock->expects(self::once())
            ->method('rankAndSortDuplicates')
            ->will(self::returnValue(true));

        $filterDuplicateCheckMock->findDuplicates();
    }

    /**
     * @group duplicatecheck
     */
    public function testFindDuplicates_HasBeanId_AddFilterForEditsIsCalled()
    {
        $bean = $this->createMock(Lead::class);
        $bean->expects(self::any())
            ->method('ACLFieldAccess')
            ->will(self::returnValue(true));
        $bean->id = 1;

        $filterDuplicateCheckMock = $this->getMockBuilder(FilterDuplicateCheck::class)
            ->setMethods([
                'buildDupeCheckFilter',
                'addFilterForEdits',
                'callFilterApi',
                'rankAndSortDuplicates',
            ])->setConstructorArgs([
                $bean,
                $this->metadata,
            ])->getMock();

        $filterDuplicateCheckMock->expects(self::once())
            ->method('buildDupeCheckFilter')
            ->will(self::returnValue(['whatever']));

        // addFilterForEdits should be called if the bean has an id
        $filterDuplicateCheckMock->expects(self::once())
            ->method('addFilterForEdits');

        $filterDuplicateCheckMock->expects(self::once())
            ->method('callFilterApi')
            ->will(self::returnValue(true));

        $filterDuplicateCheckMock->expects(self::once())
            ->method('rankAndSortDuplicates')
            ->will(self::returnValue(true));

        $filterDuplicateCheckMock->findDuplicates();
    }

    /**
     * @group duplicatecheck
     */
    public function testFindDuplicates_RankAndSortDuplicatesReordersTheResults()
    {
        $bean = $this->createMock(Lead::class);
        $bean->expects(self::any())
            ->method('ACLFieldAccess')
            ->will(self::returnValue(true));
        $bean->last_name = 'Griffin';
        $bean->first_name = 'Pete';
        $bean->account_name = 'Petoria';

        $filterDuplicateCheckMock = $this->getMockBuilder(FilterDuplicateCheck::class)
            ->setMethods([
                'callFilterApi',
            ])->setConstructorArgs([
                $bean,
                $this->metadata,
            ])->getMock();

        $duplicate1 = [
            'id' => '1',
            'last_name' => 'Griffin',
            'first_name' => 'Peter',
            'status' => 'New',
            'account_name' => 'Petoria',
        ];

        $duplicate2 = [
            'id' => '2',
            'last_name' => 'Griffin',
            'first_name' => 'Pete',
            'status' => '',
            'account_name' => 'Petoria',
        ];

        $results = [
            'records' => [
                $duplicate1,
                $duplicate2,
            ],
        ];

        $filterDuplicateCheckMock->expects(self::once())
            ->method('callFilterApi')
            ->will(self::returnValue($results));

        $expected = [
            'records' => [
                $duplicate2,
                $duplicate1,
            ],
        ];
        $actual = $filterDuplicateCheckMock->findDuplicates();
        self::assertEquals(
            $expected['records'][0]['id'],
            $actual['records'][0]['id'],
            'The duplicate records should have swapped places based on their duplicate_check_rank values.'
        );
    }

    /**
     * @group duplicatecheck
     */
    public function testBuildDupeCheckFilter_ReplacesFirstName_ReplacesLastName_RemovesAccountName()
    {
        $bean = $this->createMock(Lead::class);
        $bean->expects(self::any())
            ->method('ACLFieldAccess')
            ->will(self::returnValue(true));
        $bean->last_name = 'Griffin';
        $bean->first_name = 'Peter';
        $filterDuplicateCheckCaller = new FilterDuplicateCheckCaller($bean, $this->metadata);

        $expected = [
            [
                '$and' => [
                    [
                        '$or' => [
                            [
                                'status' => [
                                    '$not_equals' => 'Converted',
                                ],
                            ],
                            [
                                'status' => [
                                    '$is_null' => '',
                                ],
                            ],
                        ],
                    ],
                    [
                        '$or' => [
                            [
                                '$and' => [
                                    [
                                        'first_name' => [
                                            '$starts' => $bean->first_name,
                                        ],
                                    ],
                                    [
                                        'last_name' => [
                                            '$starts' => $bean->last_name,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $actual = $filterDuplicateCheckCaller->buildDupeCheckFilterCaller();

        // compare the complete arrays
        self::assertEquals(
            $expected,
            $actual,
            'The original filters were lost or the new filter is not constructed properly.'
        );
    }

    /**
     * @group duplicatecheck
     */
    public function testBuildDupeCheckFilter_NoDataForAllFieldsInSection_RemovesWholeSection()
    {
        $bean = $this->createMock(Lead::class);
        $bean->expects(self::any())
            ->method('ACLFieldAccess')
            ->will(self::returnValue(true));
        $filterDuplicateCheckCaller = new FilterDuplicateCheckCaller($bean, $this->metadata);

        $expected = [
            [
                '$and' => [
                    [
                        '$or' => [
                            [
                                'status' => [
                                    '$not_equals' => 'Converted',
                                ],
                            ],
                            [
                                'status' => [
                                    '$is_null' => '',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $actual = $filterDuplicateCheckCaller->buildDupeCheckFilterCaller();

        // compare the complete arrays
        self::assertEquals(
            $expected,
            $actual,
            'The original filters were lost or the new filter is not constructed properly.'
        );
    }

    /**
     * @group duplicatecheck
     */
    public function testAddFilterForEdits_AddsANotEqualsFilterToTheFilterArrayToPreventMatchesOnTheSpecifiedId()
    {
        $bean = $this->createMock(Lead::class);
        $bean->expects(self::any())
            ->method('ACLFieldAccess')
            ->will(self::returnValue(true));
        $bean->id = '1';
        $filterDuplicateCheckCaller = new FilterDuplicateCheckCaller($bean, $this->metadata);
        $filter = $filterDuplicateCheckCaller->buildDupeCheckFilterCaller(); // need to build the filter first

        $expected = [
            [
                '$and' => [
                    [
                        'id' => [
                            '$not_equals' => $bean->id,
                        ],
                    ],
                    $filter,
                ],
            ],
        ];
        $actual = $filterDuplicateCheckCaller->addFilterForEditsCaller($filter, $bean->id);

        // compare the complete arrays
        self::assertEquals(
            $expected,
            $actual,
            'The original filters were lost or the new filter is not constructed properly.'
        );

        // make sure the id filter was added
        self::assertEquals(
            $expected[0]['$and'][0]['id']['$not_equals'],
            $actual[0]['$and'][0]['id']['$not_equals'],
            'The additional not-equals filter was not added.'
        );
    }

    public function testBuildDupeCheckFilterCallerRemovesFieldsUserDoesntHaveAccessTo()
    {
        $bean = $this->createMock(Lead::class);
        $bean->expects($this->exactly(2))
            ->method('ACLFieldAccess')
            ->will(
                $this->onConsecutiveCalls(true, false)
            );
        $bean->name = 'Fred';

        $metadata = [
            'filter_template' => [
                [
                    '$and' => [
                        ['name' => ['$starts' => '$name']],
                        ['sales_status' => ['$not_equals' => 'Closed Lost']],
                    ],
                ],
            ],
        ];

        $filterDuplicateCheckCaller = new FilterDuplicateCheck($bean, $metadata);


        $filter = SugarTestReflection::callProtectedMethod(
            $filterDuplicateCheckCaller,
            'buildDupeCheckFilter',
            [$metadata['filter_template']]
        );

        $this->assertEquals(1, safeCount($filter));
    }
}

// need to make sure SugarApi is included when extending FilterDuplicateCheck to avoid a fatal error

class FilterDuplicateCheckCaller extends FilterDuplicateCheck
{
    public function buildDupeCheckFilterCaller()
    {
        return $this->buildDupeCheckFilter($this->filterTemplate);
    }

    public function addFilterForEditsCaller($filter, $id)
    {
        return $this->addFilterForEdits($filter, $id);
    }
}
