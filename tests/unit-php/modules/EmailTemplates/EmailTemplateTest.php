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

namespace Sugarcrm\SugarcrmTestsUnit\modules\EmailTemplates;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \EmailTemplate
 */
class EmailTemplateTest extends TestCase
{
    public function checkStringHasVariablesProvider()
    {
        return [
            'no variables' => [
                '<p>Hello Test,</p>',
                false,
            ],
            'no variables because the an underscore is expected in the field name' => [
                '<p>Hello $accountName,</p>',
                false,
            ],
            'no variables because it is a dollar value' => [
                '<p>Hello $1,000,000,</p>',
                false,
            ],
            'has variable matching $account_name' => [
                '<p>Hello $account_name,</p>',
                true,
            ],
            'has variable matching $accountName_field' => [
                '<p>Hello $accountName_field,</p>',
                true,
            ],
            'has variable matching $account_fieldName' => [
                '<p>Hello $account_fieldName,</p>',
                true,
            ],
            'has variable matching $accountName_fieldName' => [
                '<p>Hello $accountName_fieldName,</p>',
                true,
            ],
            'has variable matching a custom field' => [
                '<p>Hello $accountName_fieldName_c,</p>',
                true,
            ],
            'has variable with lots of underscores' => [
                '<p>Hello $accountName_fieldName_is_something_else,</p>',
                true,
            ],
            'has variable that includes numerical digits' => [
                '<p>Hello $account_name1,</p>',
                true,
            ],
        ];
    }

    /**
     * @dataProvider checkStringHasVariablesProvider
     * @covers ::checkStringHasVariables
     */
    public function testCheckStringHasVariables($tplString, $expected)
    {
        $actual = TestReflection::callProtectedMethod('\\EmailTemplate', 'checkStringHasVariables', [$tplString]);
        $this->assertSame($expected, $actual);
    }
}
