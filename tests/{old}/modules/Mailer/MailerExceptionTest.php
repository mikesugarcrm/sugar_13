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
 * @group email
 * @group mailer
 * @group mailerexceptiontest
 */
class MailerExceptionTest extends TestCase
{
    /**
     * @var mixed[]
     */
    public $mod_strings;

    protected function setUp(): void
    {
        global $current_language;
        $this->mod_strings = return_module_language($current_language, 'Emails');
    }

    public function testGetUserFriendlyMessage_ErrorCodeExists_ReturnsMappedModuleString()
    {
        $expected = $this->mod_strings['LBL_INVALID_EMAIL'];

        $exception = new MailerException('foo', MailerException::InvalidEmailAddress);
        $result = $exception->getUserFriendlyMessage();

        $this->assertEquals($expected, $result, 'Should map to the correct error message');
    }

    public function testGetUserFriendlyMessage_ErrorCodeDoesNotExistInMap_ReturnsDefaultModuleString()
    {
        $expected = $this->mod_strings['LBL_INTERNAL_ERROR'];

        $exception = new MailerException('foo', 0);
        $result = $exception->getUserFriendlyMessage();

        $this->assertEquals($expected, $result, 'Should map to the correct error message');
    }
}
