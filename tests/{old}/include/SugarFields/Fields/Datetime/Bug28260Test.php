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

$timedate = TimeDate::getInstance();

class Bug28260Test extends TestCase
{
    private $user;

    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->user = $GLOBALS['current_user'];
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->user);
        unset($GLOBALS['current_user']);
    }

    public static function providerEmailTemplateFormat()
    {
        return [
            ['10/11/2010 13:00', '10/11/2010 13:00', 'm/d/Y', 'H:i'],
            ['11/10/2010 13:00', '11/10/2010 13:00', 'd/m/Y', 'H:i'],
            ['2010-10-11 13:00:00', '10/11/2010 13:00', 'm/d/Y', 'H:i'],
            ['2010-10-11 13:00:00', '11/10/2010 13:00', 'd/m/Y', 'H:i'],
            ['2010-10-11 13:00:00', '10-11-2010 13:00', 'm-d-Y', 'H:i'],
            ['2010-10-11 13:00:00', '11-10-2010 13:00', 'd-m-Y', 'H:i'],
            ['2010-10-11 13:00:00', '2010-10-11 13:00', 'Y-m-d', 'H:i'],
        ];
    }

    /**
     * @dataProvider providerEmailTemplateFormat
     */
    public function testEmailTemplateFormat($unformattedValue, $expectedValue, $dateFormat, $timeFormat)
    {
        $GLOBALS['sugar_config']['default_date_format'] = $dateFormat;
        $GLOBALS['sugar_config']['default_time_format'] = $timeFormat;
        $GLOBALS['current_user']->setPreference('datef', $dateFormat);
        $GLOBALS['current_user']->setPreference('timef', $timeFormat);

        $sfr = SugarFieldHandler::getSugarField('datetime');
        $formattedValue = $sfr->getEmailTemplateValue($unformattedValue, ['type' => 'datetime'], ['notify_user' => $this->user]);

        $this->assertSame($expectedValue, $formattedValue);
    }
}
