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

class SugarFieldRadioenumTest extends TestCase
{
    private $testingArray = [
        'key' => 'value',
    ];
    private $testKey = 'key';
    private $testValue = 'value';
    private $testingArrayName = 'new_radio_list';
    private $testingFieldType = 'Radioenum';

    protected function setUp(): void
    {
        global $app_list_strings;
        $app_list_strings[$this->testingArrayName] = $this->testingArray;
    }

    protected function tearDown(): void
    {
        $app_list_strings = [];
        if (!empty($app_list_strings[$this->testingArrayName])) {
            unset($app_list_strings[$this->testingArrayName]);
        }
    }

    public function testEmailTemplateFormat()
    {
        $radioEnumClass = new SugarFieldRadioenum($this->testingFieldType);
        $actualResult = $radioEnumClass->getEmailTemplateValue($this->testKey, ['options' => $this->testingArrayName]);
        $this->assertEquals($this->testValue, $actualResult);
    }
}
