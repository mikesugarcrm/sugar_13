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

class FixUpFormattingTest extends TestCase
{
    public $time_date;
    protected $myBean;

    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $this->myBean = new SugarBean();

        $this->myBean->field_defs = [
            'id' => ['name' => 'id', 'vname' => 'LBL_ID', 'type' => 'id', 'required' => true,],
            'name' => ['name' => 'name', 'vname' => 'LBL_NAME', 'type' => 'varchar', 'len' => '255', 'required' => true,],
            'bool_field' => ['name' => 'bool_field', 'vname' => 'LBL_BOOL_FIELD', 'type' => 'bool',],
            'int_field' => ['name' => 'int_field', 'vname' => 'LBL_INT_FIELD', 'type' => 'int',],
            'float_field' => ['name' => 'float_field', 'vname' => 'LBL_FLOAT_FIELD', 'type' => 'float', 'precision' => 2,],
            'date_field' => ['name' => 'date_field', 'vname' => 'LBL_DATE_FIELD', 'type' => 'date',],
            'time_field' => ['name' => 'time_field', 'vname' => 'LBL_TIME_FIELD', 'type' => 'time',],
            'datetime_field' => ['name' => 'datetime_field', 'vname' => 'LBL_DATETIME_FIELD', 'type' => 'datetime',],
        ];

        $this->myBean->id = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
        $this->myBean->name = 'Fake Bean';
        $this->myBean->bool_field = 1;
        $this->myBean->int_field = 2001;
        $this->myBean->float_field = 20.01;
        $this->myBean->date_field = '2001-07-28';
        $this->myBean->time_field = '21:19:37';
        $this->myBean->datetime_field = '2001-07-28 21:19:37';
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($this->time_date);
    }

    public function providerBoolFixups()
    {
        return [
            [true, true],
            [false, false],
            ['', false],
            [1, true],
            [0, false],
            ['1', true],
            ['0', false],
            ['true', true],
            ['false', false],
            ['on', true],
            ['off', false],
            ['yes', true],
            ['no', false],
        ];
    }

    /**
     * @ticket 34562
     * @dataProvider providerBoolFixups
     */
    public function testBoolFixups($from, $to)
    {
        $this->myBean->bool_field = $from;
        $this->myBean->fixUpFormatting();
        $this->assertEquals($to, $this->myBean->bool_field, 'fixUpFormatting did not adjust from (' . gettype($from) . ') "' . $from . '"');
    }

    /**
     * @group bug43321
     */
    public function testStringNULLFixups()
    {
        $bean = new SugarBean();

        $bean->field_defs = ['date_field' => ['type' => 'date'],
            'datetime_field' => ['type' => 'datetime'],
            'time_field' => ['type' => 'time'],
            'datetimecombo_field' => ['type' => 'datetimecombo'],
        ];
        $bean->date_field = 'NULL';
        $bean->datetime_field = 'NULL';
        $bean->time_field = 'NULL';
        $bean->datetimecombo_field = 'NULL';
        $bean->fixUpFormatting();
        $this->assertEquals('', $bean->date_field, 'fixUpFormatting did not reset string NULL for date');
        $this->assertEquals('', $bean->datetime_field, 'fixUpFormatting did not reset string NULL for time');
        $this->assertEquals('', $bean->time_field, 'fixUpFormatting did not reset string NULL for datetime');
        $this->assertEquals('', $bean->datetimecombo_field, 'fixUpFormatting did not reset string NULL for datetimecombo');
    }
}
