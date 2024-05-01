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

use Sugarcrm\Sugarcrm\Reports\AccessRules\Rules\ViewRightsRule;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Reports\Constants\ReportType;
use SugarApiExceptionNotAuthorized;
use User;
use SugarBean;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Reports\AccessRules\ExportRightsRule
 */
class ExportRightsRuleTest extends TestCase
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
        $savedReportMock = $this->getMockBuilder(SugarBean::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['ACLAccess'])
            ->getMock();

        $demoContent = [
            'report_type' => ReportType::ROWSANDCOLUMNS,
            'full_table_list' => [],
            'display_columns' => [],
        ];
        $savedReportMock->content = json_encode($demoContent);

        $rule = new ViewRightsRule($this->userMock);
        $ruleReturn = $rule->validate($savedReportMock);
        $this->assertTrue($ruleReturn);
    }
}
