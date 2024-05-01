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

require_once 'modules/Calls/CallHelper.php';

class CallHelperTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    public function providerGetDurationMinutesOptions()
    {
        return [
            [
                'EditView',
                <<<EOHTML
<select id="duration_minutes" onchange="SugarWidgetScheduler.update_time();" name="duration_minutes">
<OPTION value='0'>00</OPTION>
<OPTION selected value='15'>15</OPTION>
<OPTION value='30'>30</OPTION>
<OPTION value='45'>45</OPTION></select>
EOHTML,
            ],
            [
                'MassUpdate',
                <<<EOHTML
<select id="duration_minutes" name="duration_minutes">
<OPTION value='0'>00</OPTION>
<OPTION selected value='15'>15</OPTION>
<OPTION value='30'>30</OPTION>
<OPTION value='45'>45</OPTION></select>
EOHTML,
            ],
            [
                'QuickCreate',
                <<<EOHTML
<select id="duration_minutes" onchange="SugarWidgetScheduler.update_time();" name="duration_minutes">
<OPTION value='0'>00</OPTION>
<OPTION selected value='15'>15</OPTION>
<OPTION value='30'>30</OPTION>
<OPTION value='45'>45</OPTION></select>
EOHTML,
            ],
            [
                'wirelessedit',
                <<<EOHTML
<select id="duration_minutes" name="duration_minutes">
<OPTION value='0'>00</OPTION>
<OPTION selected value='15'>15</OPTION>
<OPTION value='30'>30</OPTION>
<OPTION value='45'>45</OPTION></select>
EOHTML,
            ],
            ['DetailView', '15'],
        ];
    }

    /**
     * @dataProvider providerGetDurationMinutesOptions
     */
    public function testGetDurationMinutesOptions(
        $view,
        $returnValue
    ) {

        $focus = new Call();

        $this->assertEquals(
            getDurationMinutesOptions($focus, '', '', $view),
            $returnValue
        );
    }

    public function testGetDurationMinutesOptionsNonDefaultValue()
    {
        $focus = new Call();
        $focus->duration_minutes = '30';

        $this->assertEquals(
            getDurationMinutesOptions($focus, '', '', 'DetailView'),
            $focus->duration_minutes
        );
    }

    public function testGetDurationMinutesOptionsFromRequest()
    {
        $focus = new Call();
        $_REQUEST['duration_minutes'] = '45';

        $this->assertEquals(
            getDurationMinutesOptions($focus, '', '', 'DetailView'),
            $_REQUEST['duration_minutes']
        );

        unset($_REQUEST['duration_minutes']);
    }

    public function testGetDurationMinutesOptionsOtherValues()
    {
        $focus = new Call();
        $focus->date_start = null;
        $focus->duration_hours = null;
        $focus->minutes_value_default = null;

        getDurationMinutesOptions($focus, '', '', 'DetailView');

        $this->assertEquals($focus->date_start, $GLOBALS['timedate']->to_display_date(gmdate($GLOBALS['timedate']->get_date_time_format())));
        $this->assertEquals($focus->duration_hours, '0');
        $this->assertEquals($focus->duration_minutes, '1');
    }

    public function providerGetReminderTime()
    {
        return [
            [
                'EditView',
                <<<EOHTML
<select id="reminder_time" name="reminder_time">
<OPTION value='60'>1 minute prior</OPTION>
<OPTION value='300'>5 minutes prior</OPTION>
<OPTION value='600'>10 minutes prior</OPTION>
<OPTION value='900'>15 minutes prior</OPTION>
<OPTION value='1800'>30 minutes prior</OPTION>
<OPTION value='3600'>1 hour prior</OPTION></select>
EOHTML,
            ],
            [
                'MassUpdate',
                <<<EOHTML
<select id="reminder_time" name="reminder_time">
<OPTION value='60'>1 minute prior</OPTION>
<OPTION value='300'>5 minutes prior</OPTION>
<OPTION value='600'>10 minutes prior</OPTION>
<OPTION value='900'>15 minutes prior</OPTION>
<OPTION value='1800'>30 minutes prior</OPTION>
<OPTION value='3600'>1 hour prior</OPTION></select>
EOHTML,
            ],
            [
                'SubpanelCreates',
                <<<EOHTML
<select id="reminder_time" name="reminder_time">
<OPTION value='60'>1 minute prior</OPTION>
<OPTION value='300'>5 minutes prior</OPTION>
<OPTION value='600'>10 minutes prior</OPTION>
<OPTION value='900'>15 minutes prior</OPTION>
<OPTION value='1800'>30 minutes prior</OPTION>
<OPTION value='3600'>1 hour prior</OPTION></select>
EOHTML,
            ],
            [
                'QuickCreate',
                <<<EOHTML
<select id="reminder_time" name="reminder_time">
<OPTION value='60'>1 minute prior</OPTION>
<OPTION value='300'>5 minutes prior</OPTION>
<OPTION value='600'>10 minutes prior</OPTION>
<OPTION value='900'>15 minutes prior</OPTION>
<OPTION value='1800'>30 minutes prior</OPTION>
<OPTION value='3600'>1 hour prior</OPTION></select>
EOHTML,
            ],
            [
                'wirelessedit',
                <<<EOHTML
<select id="reminder_time" name="reminder_time">
<OPTION value='60'>1 minute prior</OPTION>
<OPTION value='300'>5 minutes prior</OPTION>
<OPTION value='600'>10 minutes prior</OPTION>
<OPTION value='900'>15 minutes prior</OPTION>
<OPTION value='1800'>30 minutes prior</OPTION>
<OPTION value='3600'>1 hour prior</OPTION></select>
EOHTML,
            ],
            ['DetailView', ''],
        ];
    }
}
