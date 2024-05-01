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

namespace Sugarcrm\SugarcrmTestsUnit\src\Reports\AccessRules\Rules;

use Sugarcrm\Sugarcrm\Reports\AccessRules\Rules\AccessFieldsRightsRule;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Reports\Constants\ReportType;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use User;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Reports\AccessRules\AccessFieldsRightsRule
 */
class AccessFieldsRightsRuleTest extends TestCase
{
    /**
     * User
     */
    protected $userMock;

    /**
     * string
     */
    protected $userIdTest = 'testUser';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isAdmin'])
            ->getMock();
        $this->userMock->id = $this->userIdTest;
        $this->userMock->is_admin = '1';
        $this->userMock->expects($this->any())
            ->method('isAdmin')
            ->will($this->returnValue(true));
    }

    /**
     * validate function
     */
    public function testValidate()
    {
        $savedReportMock = $this->getMockBuilder(Report::class)
            ->disableOriginalConstructor()
            ->getMock();

        $demoContent = [
            'report_type' => ReportType::ROWSANDCOLUMNS,
            'full_table_list' => [],
            'display_columns' => [],
        ];
        $savedReportMock->content = json_encode($demoContent);
        $rule = new AccessFieldsRightsRule($this->userMock);

        $validationResult = $rule->validate($savedReportMock);

        $this->assertTrue($validationResult);
    }

    /**
     * extractFiltersLeafs function
     */
    public function testExtractFiltersLeafs()
    {
        $rule = new AccessFieldsRightsRule($this->userMock);
        $filters = [
            'operator' => 'AND',
            [
                'name' => 'date_entered',
                'table_key' => 'self',
                'qualifier_name' => 'tp_last_7_days',
                'runtime' => 1,
                'input_name0' => 'tp_last_7_days',
                'input_name1' => 'on',
            ],
            [
                'name' => 'first_response_sla_met',
                'table_key' => 'self',
                'qualifier_name' => 'is',
                'input_name0' => [
                    'No',
                ],
            ],
        ];

        $expected = [
            [
                'name' => 'date_entered',
                'table_key' => 'self',
                'qualifier_name' => 'tp_last_7_days',
                'runtime' => 1,
                'input_name0' => 'tp_last_7_days',
                'input_name1' => 'on',
            ],
            [
                'name' => 'first_response_sla_met',
                'table_key' => 'self',
                'qualifier_name' => 'is',
                'input_name0' => [
                    'No',
                ],
            ],
        ];
        $leafs = TestReflection::callProtectedMethod($rule, 'extractFiltersLeafs', [$filters]);
        $this->assertEquals($expected, $leafs);
    }
}
