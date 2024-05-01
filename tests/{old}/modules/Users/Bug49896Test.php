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

class Bug49896Test extends TestCase
{
    private $passwordSetting;
    private $currentUser;

    protected function setUp(): void
    {
        if (isset($GLOBALS['sugar_config']['passwordsetting'])) {
            $this->passwordSetting = $GLOBALS['sugar_config']['passwordsetting'];
        }
        $GLOBALS['sugar_config']['passwordsetting'] = ['onenumber' => 1,
            'onelower' => 1,
            'oneupper' => 1,
            'onespecial' => 1,
            'minpwdlength' => 6,
            'maxpwdlength' => 15,
        ];
        $this->currentUser = SugarTestUserUtilities::createAnonymousUser(false);
    }

    protected function tearDown(): void
    {
        if (!empty($this->passwordSetting)) {
            $GLOBALS['sugar_config']['passwordsetting'] = $this->passwordSetting;
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testMinLength()
    {
        $result = $this->currentUser->check_password_rules('Tes1!');
        $this->assertEquals(false, $result, 'Assert that min length rule is checked');
    }

    public function testMaxLength()
    {
        $result = $this->currentUser->check_password_rules('Tester123456789!');
        $this->assertEquals(false, $result, 'Assert that max length rule is checked');
    }

    public function testOneNumber()
    {
        $result = $this->currentUser->check_password_rules('Tester!');
        $this->assertEquals(false, $result, 'Assert that one number rule is checked');
    }

    public function testOneLower()
    {
        $result = $this->currentUser->check_password_rules('TESTER1!');
        $this->assertEquals(false, $result, 'Assert that one lower rule is checked');
    }

    public function testOneUpper()
    {
        $result = $this->currentUser->check_password_rules('tester1!');
        $this->assertEquals(false, $result, 'Assert that one upper rule is checked');
    }

    public function testOneSpecial()
    {
        $result = $this->currentUser->check_password_rules('Tester1');
        $this->assertEquals(false, $result, 'Assert that one special rule is checked');
    }

    public function testCustomRegex()
    {
        $GLOBALS['sugar_config']['passwordsetting']['customregex'] = '/^T/';
        $result = $this->currentUser->check_password_rules('tester1!');
        $this->assertEquals(false, $result, 'Assert that custom regex is checked');
    }

    public function testAllCombinations()
    {
        $result = $this->currentUser->check_password_rules('Tester1!');
        $this->assertEquals(true, $result, 'Assert that all rules are checked and passed');
    }
}
