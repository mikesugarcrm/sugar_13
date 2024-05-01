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

class HintUsersHookTest extends TestCase
{
    /** @var HintUsersHook */
    protected $hook;

    protected function setUp(): void
    {
        $this->hook = new Sugarcrm\Sugarcrm\modules\Users\HintUsersHook();
    }

    public function testGetPrimaryEmail()
    {
        $emails = [
            [
                'email_address' => 'email1',
                'primary_address' => false,
            ],
            [
                'email_address' => 'email2',
                'primary_address' => true,
            ],
        ];

        $res = SugarTestReflection::callProtectedMethod($this->hook, 'getPrimaryEmail', [$emails]);
        $this->assertEquals('email2', $res);
    }

    public function testIsHintUser()
    {
        $licenses = [
            'CURRENT', 'HINT',
        ];

        $res = SugarTestReflection::callProtectedMethod($this->hook, 'isHintUser', [$licenses]);
        $this->assertEquals(true, $res);
    }
}
