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

class SugarApiExceptionTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['app_strings']);
    }

    public function testTranslatedExceptionMessages()
    {
        global $app_strings;
        $ex = new SugarApiException();
        $this->assertEquals($ex->getMessage(), $app_strings['EXCEPTION_UNKNOWN_EXCEPTION'], 'Default error message');
        $app_strings['EXCEPTION_TEST'] = 'Hey {0}, How you doing?';
        $ex = new SugarApiException('EXCEPTION_TEST', ['Matt']);
        $this->assertEquals($ex->getMessage(), 'Hey Matt, How you doing?', 'String formatting');
    }
}
