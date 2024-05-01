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

class AuthenticateTest extends TestCase
{
    /**
     * @var mixed
     */
    public $sugar_config_old;

    protected function setUp(): void
    {
        $GLOBALS['app'] = new SugarApplication();
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->sugar_config_old = $GLOBALS['sugar_config'];
        $_POST['user_name'] = 'foo';
        $_POST['user_password'] = 'bar';
        $_SESSION['authenticated_user_id'] = true;
        $_SESSION['hasExpiredPassword'] = false;
        $_SESSION['isMobile'] = null;
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['current_user']);
        $GLOBALS['sugar_config'] = $this->sugar_config_old;
        unset($_REQUEST['login_module']);
        unset($_REQUEST['login_action']);
        unset($_REQUEST['login_record']);
        unset($_REQUEST['user_name']);
        unset($_REQUEST['user_password']);
        unset($_SESSION['authenticated_user_id']);
        unset($_SESSION['hasExpiredPassword']);
        unset($_SESSION['isMobile']);
    }

    public function testLoginRedirectIfAuthenicationFails()
    {
        $_SESSION['authenticated_user_id'] = null;

        $authController = $this->createMock('AuthenticationController');

        $url = '';
        require 'modules/Users/Authenticate.php';

        $this->assertEquals(
            'Location: index.php?module=Users&action=Login',
            $url
        );
    }

    public function testDefaultAuthenicationRedirect()
    {
        unset($GLOBALS['sugar_config']['default_module']);
        unset($GLOBALS['sugar_config']['default_action']);
        unset($_POST['login_module']);
        unset($_POST['login_action']);
        unset($_POST['login_record']);

        $authController = $this->createMock('AuthenticationController');

        $url = '';
        require 'modules/Users/Authenticate.php';

        $this->assertEquals(
            'Location: index.php?module=Home&action=index',
            $url
        );
    }

    public function testDefaultAuthenicationRedirectGivenLoginParameters()
    {
        unset($GLOBALS['sugar_config']['default_module']);
        unset($GLOBALS['sugar_config']['default_action']);
        $_POST['login_module'] = 'foo';
        $_POST['login_action'] = 'bar';
        $_POST['login_record'] = '123';

        $authController = $this->createMock('AuthenticationController');

        $url = '';
        require 'modules/Users/Authenticate.php';

        $this->assertEquals(
            'Location: index.php?module=foo&action=bar&record=123',
            $url
        );
    }

    public function testDefaultAuthenicationRedirectGivenDefaultSettings()
    {
        $GLOBALS['sugar_config']['default_module'] = 'dog';
        $GLOBALS['sugar_config']['default_action'] = 'cat';
        unset($_POST['login_module']);
        unset($_POST['login_action']);
        unset($_POST['login_record']);

        $authController = $this->createMock('AuthenticationController');

        $url = '';
        require 'modules/Users/Authenticate.php';

        $this->assertEquals(
            'Location: index.php?module=dog&action=cat',
            $url
        );
    }
}
