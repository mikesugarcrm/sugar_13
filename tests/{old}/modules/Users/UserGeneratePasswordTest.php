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
 * UserGeneratePasswordTest
 *
 * This class runs a series of tests against the generatePassword static function in the Users class.
 * @author Collin Lee
 */
class UserGeneratePasswordTest extends TestCase
{
    private $passwordSetting;

    protected function setUp(): void
    {
        if (isset($GLOBALS['sugar_config']['passwordsetting'])) {
            $this->passwordSetting = $GLOBALS['sugar_config']['passwordsetting'];
        }
        $GLOBALS['sugar_config']['passwordsetting'] = ['onenumber' => 0,
            'onelower' => 0,
            'oneupper' => 0,
            'onespecial' => 0,
            'minpwdlength' => 6,
        ];
    }

    protected function tearDown(): void
    {
        if (!empty($this->passwordSetting)) {
            $GLOBALS['sugar_config']['passwordsetting'] = $this->passwordSetting;
        }
    }

    public function testUserGeneratePasswordOneNumber()
    {
        $GLOBALS['sugar_config']['passwordsetting']['onenumber'] = '1';
        $password = User::generatePassword();
        $this->assertMatchesRegularExpression('/\d/', $password, 'Assert that we have at least one number in the generated password');
    }

    public function testUserGeneratePasswordOneLower()
    {
        $GLOBALS['sugar_config']['passwordsetting']['onelower'] = '1';
        $password = User::generatePassword();
        $this->assertMatchesRegularExpression('/[a-z]/', $password, 'Assert that we have at least one lower case letter in the generated password');
    }

    public function testUserGeneratePasswordOneUpper()
    {
        $GLOBALS['sugar_config']['passwordsetting']['oneupper'] = '1';
        $password = User::generatePassword();
        $this->assertMatchesRegularExpression('/[A-Z]/', $password, 'Assert that we have at least one upper case letter in the generated password');
    }

    public function testUserGeneratePasswordOneSpecial()
    {
        $GLOBALS['sugar_config']['passwordsetting']['onespecial'] = '1';
        $password = User::generatePassword();
        $this->assertMatchesRegularExpression('/[\~\!\@\#\$\%\^\&\*\(\)\_\+\=\-\{\}\|]/', $password, 'Assert that we have at least one special letter in the generated password');
    }

    public function testUserGeneratedPasswordMinimumLength()
    {
        $GLOBALS['sugar_config']['passwordsetting']['minpwdlength'] = 10;
        $password = User::generatePassword();
        $this->assertTrue(strlen($password) >= 10, 'Assert that the password minimum length of 10 is respected');

        $GLOBALS['sugar_config']['passwordsetting']['minpwdlength'] = 5;
        $password = User::generatePassword();
        $this->assertTrue(strlen($password) >= 6, 'Assert that the password minimum length is at least 6');
    }

    public function testAllCombinationsEnabled()
    {
        $GLOBALS['sugar_config']['passwordsetting'] = [
            'onenumber' => '1',
            'onelower' => '1',
            'oneupper' => '1',
            'onespecial' => '1',
            'minpwdlength' => 10,
        ];

        $password = User::generatePassword();
        $this->assertMatchesRegularExpression('/\d/', $password, 'Assert that we have at least one number in the generated password');
        $this->assertMatchesRegularExpression('/[a-z]/', $password, 'Assert that we have at least one lower case letter in the generated password');
        $this->assertMatchesRegularExpression('/[A-Z]/', $password, 'Assert that we have at least one upper case letter in the generated password');
        $this->assertMatchesRegularExpression('/[\~\!\@\#\$\%\^\&\*\(\)\_\+\=\-\{\}\|]/', $password, 'Assert that we have at least one special letter in the generated password');
        $this->assertTrue(strlen($password) >= 10, 'Assert that the password minimum length of 10 is respected');
    }
}
