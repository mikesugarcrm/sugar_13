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

class Bug41114Test extends TestCase
{
    private $user;

    protected function setUp(): void
    {
        $this->user = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->user);
    }

    public static function providerEmailTemplateFormat()
    {
        return [
            ['2010-10-10 13:00:00', '2010/10/10 01:00PM', 'Y/m/d', 'h:iA'],
            ['2010-10-11 13:00:00', '2010/10/11 13:00', 'Y/m/d', 'H:i'],

            ['2011-03-25 01:05:22', '25.03.2011 01:05AM', 'd.m.Y', 'h:iA'],
            ['2011-04-21 01:05:22', '21.04.2011 01:05', 'd.m.Y', 'H:i'],

            ['', '', 'Y-m-d', 'h:iA'],
            ['', '', 'Y-m-d', 'H:i'],

        ];
    }

    /**
     * @dataProvider providerEmailTemplateFormat
     */
    public function testEmailTemplateFormat($unformattedValue, $expectedValue, $dateFormat, $timeFormat)
    {
        $GLOBALS['sugar_config']['default_date_format'] = $dateFormat;
        $GLOBALS['sugar_config']['default_time_format'] = $timeFormat;
        $this->user->setPreference('datef', $dateFormat);
        $this->user->setPreference('timef', $timeFormat);

        $sfr = SugarFieldHandler::getSugarField('datetimecombo');
        $formattedValue = $sfr->getEmailTemplateValue($unformattedValue, [], ['notify_user' => $this->user]);

        $this->assertEquals($expectedValue, $formattedValue);
    }
}
