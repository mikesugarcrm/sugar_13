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

use Sugarcrm\Sugarcrm\Security\InputValidation\InputValidation;
use PHPUnit\Framework\TestCase;

require_once 'modules/DynamicFields/templates/Fields/TemplateField.php';

/**
 * Bug49939Test.php
 * @author Collin Lee
 *
 * This is a simple test to assert that we can correctly remove the XSS attack strings set in the help field
 * via Studio.
 */
class Bug49939Test extends TestCase
{
    /**
     * xssFields
     * This is the provider function for testPopulateFromPostWithXSSHelpField
     */
    public function xssFields()
    {
        return [
            ['<script>alert(50);</script>', ''],
            ['This is some help text', 'This is some help text'],
            ['???', '???'],
            ['Foo Foo<script type="text/javascript">alert(50);</script>Bar Bar', 'Foo FooBar Bar'],
            ['I am trying to <b>Bold</b> this!', 'I am trying to &lt;b&gt;Bold&lt;/b&gt; this!'],
            ['', ''],
            ['ä, ö, ü, å, æ, ø, å', 'ä, ö, ü, å, æ, ø, å'],
        ];
    }

    /**
     * testPopulateFromPostWithXSSHelpField
     * @dataProvider xssFields
     * @param string $badXSS The bad XSS script
     * @param string $expectedValue The expected output
     */
    public function testPopulateFromPostWithXSSHelpField($badXSS, $expectedValue)
    {
        /** @var TemplateField $tf */
        $tf = $this->createPartialMock('TemplateField', ['applyVardefRules']);
        $request = InputValidation::create([
            'help' => $badXSS,
        ], []);
        $tf->vardef_map = ['help' => 'help'];
        $tf->populateFromPost($request);
        $this->assertEquals($expectedValue, $tf->help, 'Unable to remove XSS from help field');
    }
}
