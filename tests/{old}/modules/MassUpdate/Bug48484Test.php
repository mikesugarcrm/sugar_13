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
 * @ticket 48484
 */
class Bug48484Test extends TestCase
{
    /**
     * Existing module name used to perform the test
     *
     * @var string
     */
    protected $moduleName = 'Accounts';

    /**
     * Custom field name that is tested to be considered
     *
     * @var string
     */
    protected $customFieldName = 'bug48484test_c';

    /**
     * Stub of the mass update object being tested.
     * @var
     */
    protected $massUpdate;

    /**
     * Basic range used to perform the test
     *
     * @var string
     */
    protected $range = 'this_year';

    protected function setUp(): void
    {
        $this->massUpdate = new MassUpdateStub($this->customFieldName);
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        SugarTestHelper::setUp('app_strings');
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    /**
     * Verify whether custom field values are considered during mass update
     */
    public function testModuleCustomFieldsAreConsidered()
    {
        // create search query
        $query = [
            'searchFormTab' => 'basic_search',
            $this->customFieldName . '_basic_range_choice' => $this->range,
            'range_' . $this->customFieldName . '_basic' => '[' . $this->range . ']',
        ];

        // generate SQL where clause
        $this->massUpdate->generateSearchWhere($this->moduleName, $query);

        // ensure that field name is contained in SQL where clause
        $this->assertStringContainsString($this->customFieldName, $this->massUpdate->where_clauses);
    }
}


class MassUpdateStub extends MassUpdate
{
    protected $customFieldName = 'bug48484test_c';

    public function __construct($customFieldName)
    {
        $this->customFieldName = $customFieldName;
    }

    protected function getSearchDefs($module, $metafiles = [])
    {
        return [
            $module => [
                'layout' => [
                    'basic_search' => [
                        $this->customFieldName => [
                            'type' => 'date',
                            'name' => $this->customFieldName,
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getSearchFields($module, $metafiles = [])
    {
        $customFields = [
            'range_' . $this->customFieldName,
            'start_range_' . $this->customFieldName,
            'end_range_' . $this->customFieldName,
        ];

        $searchFields = [];
        foreach ($customFields as $field) {
            $searchFields[$field] = [
                'query_type' => 'default',
                'enable_range_search' => true,
                'is_date_field' => true,
            ];
        }
        return [$module => $searchFields];
    }
}
