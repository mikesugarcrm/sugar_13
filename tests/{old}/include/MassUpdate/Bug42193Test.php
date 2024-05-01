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

class Bug42193Test extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = '1';
        $GLOBALS['current_user']->save();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['current_user']);
    }

    public function testDateConversionMassUpdate()
    {
        $emailMan = new EmailMan();

        $mass = new MassUpdate();

        $mass->setSugarBean($emailMan);
        $pattern = '/07\/22\/2011 [0-9]{2}:[0-9]{2}/';
        $this->assertMatchesRegularExpression(
            $pattern,
            $mass->date_to_dateTime('send_date_time', '07/22/2011')
        );
    }
}
