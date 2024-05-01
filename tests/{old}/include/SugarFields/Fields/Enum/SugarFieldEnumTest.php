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

class SugarFieldEnumTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    /**
     * @ticket 36744
     */
    public function testFormatEnumField()
    {
        $langpack = new SugarTestLangPackCreator();
        $langpack->setAppListString(
            'case_priority_dom',
            [
                'P1' => 'High',
                'P2' => 'Medium',
                'P3' => 'Low',
            ]
        );
        $langpack->save();

        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $fieldDef = [
            'name' => 'priority',
            'vname' => 'LBL_PRIORITY',
            'type' => 'enum',
            'options' => 'case_priority_dom',
            'len' => 25,
            'audited' => true,
            'comment' => 'The priority of the case',
        ];
        $field_value = 'P2';

        $sfr = SugarFieldHandler::getSugarField('enum');

        $this->assertEquals(trim($sfr->formatField($field_value, $fieldDef)), 'Medium');
    }

    /**
     * this tests that functions are being evaluated on fields of type enum that are used in email templates
     * @ticket 36744
     */
    public function testGetEmailTemplateValue()
    {
        //vardef definition, note that function is called
        $fieldDef = [
            'name' => 'type_dropdown',
            'vname' => 'LBL_TYPE',
            'type' => 'enum',
            'function' => 'getEnumTestDDVals',
        ];
        //create sugarfield of type enum and run
        $sfr = SugarFieldHandler::getSugarField('enum');
        $second = $sfr->getEmailTemplateValue('2', $fieldDef, null);

        //assert that object returned is a string and not an array
        $this->assertFalse(is_array($second), 'array was returned, string value to search for is not getting passed in');

        //assert that right value was returned
        $this->assertEquals($second, 'two', 'wrong value was returned from getEnumTestDDVals function');
    }
}


/**
 * function that gets called when enum field is being evaluated from testGetEmailTemplateValue() above
 */
function getEnumTestDDVals($numb)
{
    $numbArray = [
        '' => '',
        '1' => 'one',
        '2' => 'two',
        '3' => 'three',
    ];

    if ($numb) {
        return $numbArray[$numb];
    }

    return $numbArray;
}
