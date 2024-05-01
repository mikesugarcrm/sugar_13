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

class SugarFieldDateTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @group export
     */
    public function testExportSanitize()
    {
        $db = DBManagerFactory::getInstance();
        $timedate = TimeDate::getInstance();
        $now = $timedate->getNow();
        $isoDate = $timedate->asIso($now);
        $dbDate = $timedate->asDbDate($now);

        $expectedTime = $timedate->to_display_date($db->fromConvert($dbDate, 'date'));
        $obj = BeanFactory::newBean('Opportunities');
        $obj->date_closed = $isoDate;

        $vardef = $obj->field_defs['date_closed'];

        $field = SugarFieldHandler::getSugarField('date');
        $value = $field->exportSanitize($obj->date_closed, $vardef, $obj);
        $this->assertEquals($expectedTime, $value);

        $obj->date_closed = $dbDate;
        $value = $field->exportSanitize($obj->date_closed, $vardef, $obj);
        $this->assertEquals($expectedTime, $value);
    }
}
