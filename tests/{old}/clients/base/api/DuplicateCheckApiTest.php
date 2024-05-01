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

/**
 * @group api
 * @group duplicatecheck
 */
class DuplicateCheckApiTest extends TestCase
{
    private $copyOfLeadsDuplicateCheckVarDef;
    private $mockLeadsDuplicateCheckVarDef = [
        'enabled' => true,
        'FilterDuplicateCheck' => [
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
                                [
                                    'phone_work' => [
                                        '$equals' => '$phone_work',
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
        ],
    ];

    private $api;
    private $duplicateCheckApi;
    private $convertedLead;
    private $newLead;
    private $newLead2;
    private $newLeadFirstName = 'SugarLeadNewFirst';
    private $newLeadLastName = 'SugarLeadLast';

    // different first name
    private $newLead2FirstName = 'SugarLeadNewFirst2';

    // same last name
    private $newLead2LastName = 'SugarLeadLast';

    protected function setUp(): void
    {
        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('ACLStatic');

        $this->copyOfLeadsDuplicateCheckVarDef = $GLOBALS['dictionary']['Lead']['duplicate_check'];
        $GLOBALS['dictionary']['Lead']['duplicate_check'] = $this->mockLeadsDuplicateCheckVarDef;

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $this->api = SugarTestRestUtilities::getRestServiceMock();
        $this->duplicateCheckApi = new DuplicateCheckApi();

        //make sure any left over test leads from failed tests are removed
        $GLOBALS['db']->query('DELETE FROM leads WHERE last_name LIKE (\'SugarLead%\')');

        //create test leads
        $this->convertedLead = SugarTestLeadUtilities::createLead();
        $this->convertedLead->first_name = 'SugarLeadConvertFirst';
        $this->convertedLead->last_name = 'SugarLeadLast';
        $this->convertedLead->status = 'Converted';
        $this->convertedLead->save();

        $this->newLead = SugarTestLeadUtilities::createLead();
        $this->newLead->first_name = $this->newLeadFirstName;
        $this->newLead->last_name = $this->newLeadLastName;
        $this->newLead->save();

        $this->newLead2 = SugarTestLeadUtilities::createLead();
        $this->newLead2->first_name = $this->newLead2FirstName;
        $this->newLead2->last_name = $this->newLead2LastName;
        $this->newLead2->status = 'New'; // non-empty, non-Converted status
        $this->newLead2->save();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        $GLOBALS['dictionary']['Lead']['duplicate_check'] = $this->copyOfLeadsDuplicateCheckVarDef;
        SugarTestHelper::tearDown();
    }

    /**
     * @dataProvider duplicatesProvider
     */
    public function testCheckForDuplicates($args, $expected, $message)
    {
        $args['module'] = 'Leads';
        $results = $this->duplicateCheckApi->checkForDuplicates($this->api, $args);
        $actual = safeCount($results['records']);
        self::assertEquals($expected, $actual, $message);
    }

    public function duplicatesProvider()
    {
        return [
            [
                [
                    'first_name' => $this->newLeadFirstName,
                    'last_name' => $this->newLeadLastName,
                ],
                2,
                'Two fields passed in; should match two Leads',
            ],
            [
                [
                    'first_name' => $this->newLead2FirstName,
                    'last_name' => $this->newLead2LastName,
                ],
                1,
                'Two fields passed in; should match one Lead',
            ],
            [
                [
                    'first_name' => '',
                    'last_name' => $this->newLeadLastName,
                ],
                2,
                'One of the two fields passed in is blank; should match two Leads',
            ],
            [
                [
                    'last_name' => $this->newLeadLastName,
                ],
                2,
                "Filter omits 'first_name' since field is not passed in; should match two Leads",
            ],
            [
                [
                    'last_name' => 'DO NOT MATCH ANY LAST NAMES',
                ],
                0,
                'No duplicate matches, should returns 0 results',
            ],
        ];
    }

    public function testCheckForDuplicates_AllFilterArgumentsAreEmpty_ReturnsEmptyResultSet()
    {
        $GLOBALS['dictionary']['Lead']['duplicate_check'] = [
            'FilterDuplicateCheck' => [
                'filter_template' => [
                    [
                        'last_name' => [
                            '$starts' => '$last_name',
                        ],
                    ],
                ],
            ],
        ];

        $args = [
            'module' => 'Leads',
            'last_name' => '',
        ];
        $results = $this->duplicateCheckApi->checkForDuplicates($this->api, $args);
        self::assertEquals([], $results, 'When all arguments expected by the filter are empty, no records should be returned');
    }

    public function testCheckForDuplicates_EmptyBean()
    {
        $args = [
            'module' => 'FooModule',
        ];

        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $this->duplicateCheckApi->checkForDuplicates($this->api, $args);
    }

    public function testCheckForDuplicates_NotAuthorized()
    {
        $acldata = [];
        $args = [
            'module' => 'Leads',
        ];
        //Setting access to be denied for read
        $acldata['module']['access']['aclaccess'] = ACL_ALLOW_DISABLED;
        ACLAction::setACLData($GLOBALS['current_user']->id, $args['module'], $acldata);
        // reset cached ACLs
        SugarACL::$acls = [];

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->duplicateCheckApi->checkForDuplicates($this->api, $args);
    }

    public function testCheckForDuplicates_InvalidParameter()
    {
        $args = [
            'module' => 'Leads',
        ];

        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $duplicateCheckApi = $this->createPartialMock('DuplicateCheckApi', ['populateFromApi']);
        $duplicateCheckApi->expects($this->any())
            ->method('populateFromApi')
            ->will($this->returnValue([]));
        $duplicateCheckApi->checkForDuplicates($this->api, $args);
    }
}
