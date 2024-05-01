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

class Bug43143Test extends TestCase
{
    /**
     * @var \Opportunity|mixed
     */
    public $bean;
    /**
     * @var mixed
     */
    public $defs;
    public $timedate;

    public static function setUpBeforeClass(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    protected function setUp(): void
    {
        $this->bean = new Opportunity();
        $this->defs = $this->bean->field_defs;
        $this->timedate = $GLOBALS['timedate'];
    }

    protected function tearDown(): void
    {
        $this->bean->field_defs = $this->defs;
        $GLOBALS['timedate']->clearCache();
    }

    public function defaultDates()
    {
        return [
            ['-1 day', '2010-12-31'],
            ['now', '2011-01-01'],
            ['+1 day', '2011-01-02'],
            ['+1 week', '2011-01-08'],
            ['next monday', '2011-01-03'],
            ['next friday', '2011-01-07'],
            ['+2 weeks', '2011-01-15'],
            ['+1 month', '2011-02-01'],
            ['first day of next month', '2011-02-01'],
            ['+3 months', '2011-04-01'],
            ['+6 months', '2011-07-01'],
            ['+1 year', '2012-01-01'],
            ['first day of this month', '2011-01-01'],
            ['last day of this month', '2011-01-31'],
            ['last day of next month', '2011-02-28'],
        ];
    }

    /**
     * @dataProvider defaultDates
     * @param string $default
     * @param string $value
     */
    public function testDefaults($default, $value)
    {
        $this->timedate->allow_cache = true;
        $this->timedate->setNow($this->timedate->fromDb('2011-01-01 00:00:00'));
        $this->bean->field_defs['date_closed']['display_default'] = $default;
        $this->bean->populateDefaultValues(true);
        $this->assertEquals($value, $this->timedate->to_db_date($this->bean->date_closed));
    }

    /*
     * @group bug43143
     */
    public function testUnpopulateData()
    {
        $this->bean->field_defs['date_closed']['display_default'] = 'next friday';
        $this->bean->populateDefaultValues(true);
        $this->assertNotNull($this->bean->date_closed);
        $this->bean->unPopulateDefaultValues();
        $this->assertNull($this->bean->name);
        $this->assertNull($this->bean->date_closed);
    }
}
