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

class PopulateTimePeriodsSeedDataTest extends TestCase
{
    private $createdTimePeriods;

    protected function setUp(): void
    {
        $GLOBALS['db']->query('UPDATE timeperiods SET deleted = 1');
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query('DELETE FROM timeperiods WHERE deleted = 0');
        $GLOBALS['db']->query('UPDATE timeperiods SET deleted = 0');
    }

    public function testPopulateSeedData()
    {
        $this->createdTimePeriods = TimePeriodsSeedData::populateSeedData();
        $this->assertEquals(20, count($this->createdTimePeriods));
        $total = $GLOBALS['db']->getOne('SELECT count(id) as total FROM timeperiods WHERE deleted = 0');
        $this->assertEquals(25, $total);
    }
}
