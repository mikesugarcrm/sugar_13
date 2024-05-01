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
 * @group Bug49691
 */
class Bug49691aTest extends TestCase
{
    public $bean;
    public $sugarField;

    public $oldDate;
    public $oldTime;

    protected function setUp(): void
    {
        global $sugar_config;
        $this->bean = new Bug49691aMockBean();
        $this->sugarField = new SugarFieldDatetimecombo('Accounts');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->oldDate = $sugar_config['default_date_format'];
        $sugar_config['default_date_format'] = 'm/d/Y';
        $this->oldTime = $sugar_config['default_time_format'];
        $sugar_config['default_time_format'] = 'H:i';
    }

    protected function tearDown(): void
    {
        global $sugar_config;
        unset($GLOBALS['current_user']);
        $sugar_config['default_date_format'] = $this->oldDate;
        $sugar_config['default_time_format'] = $this->oldTime;
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->sugarField);
    }

    /**
     * @dataProvider providerFunction
     */
    public function testDBDateConversion($dateValue, $expected)
    {
        global $current_user;

        $this->bean->test_c = $dateValue;

        $inputData = ['test_c' => $dateValue];
        $field = 'test_c';
        $def = '';
        $prefix = '';

        $this->sugarField->save($this->bean, $inputData, $field, $def, $prefix);
        $this->assertNotEmpty($this->bean->test_c);
        $this->assertSame($expected, $this->bean->test_c);
    }

    public function providerFunction()
    {
        return [
            ['01/01/2012 12:00', '2012-01-01 12:00:00'],
            ['2012-01-01 12:00:00', '2012-01-01 12:00:00'],
            ['01/01/2012', '2012-01-01'],
            ['2012-01-01', '2012-01-01'],
        ];
    }
}

class Bug49691aMockBean
{
    public $test_c;
}
