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

class SugarApplicationTest extends TestCase
{
    /**
     * @var mixed
     */
    public $prevRefererList;
    private $app;
    // Run in isolation so that it doesn't mess up other tests
    public $inIsolation = true;

    protected function setUp(): void
    {
        $GLOBALS['sugar_config']['allowed_actions'] = ['custom'];
        $this->app = $this->getMockBuilder('SugarApplication')
            ->setMethods(null)
            ->getMock();
        $this->app->controller = new stdClass();
        if (isset($_SESSION['authenticated_user_theme'])) {
            unset($_SESSION['authenticated_user_theme']);
        }

        if (isset($GLOBALS['sugar_config']['http_referer'])) {
            $this->prevRefererList = $GLOBALS['sugar_config']['http_referer'];
        }

        $GLOBALS['sugar_config']['http_referer'] = ['list' => [], 'actions' => []];

        // save some globals to restore it back in tearDown
        $this->globalsBackup = [
            'moduleList' => $GLOBALS['moduleList'] ?? null,
            'modInvisList' => $GLOBALS['modInvisList'] ?? null,
            'modInvisListActivities' => $GLOBALS['modInvisListActivities'] ?? null,
        ];
    }

    private function loadUser()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $_SESSION[$GLOBALS['current_user']->user_name . '_PREFERENCES']['global']['gridline'] = 'on';
    }

    private function removeUser()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }


    protected function tearDown(): void
    {
        unset($GLOBALS['sugar_config']['allowed_actions']);
        $GLOBALS['current_language'] = $GLOBALS['sugar_config']['default_language'];

        if (isset($this->prevRefererList)) {
            $GLOBALS['sugar_config']['http_referer'] = $this->prevRefererList;
        } else {
            unset($GLOBALS['sugar_config']['http_referer']);
        }

        global $sugar_version, $sugar_db_version, $sugar_flavor, $sugar_build, $sugar_timestamp;
        require 'sugar_version.php';

        // restore previously saved globals
        foreach ($this->globalsBackup as $key => $value) {
            if ($value === null) {
                unset($GLOBALS[$key]);
            } else {
                $GLOBALS[$key] = $value;
            }
        }
    }

    public function testSetupPrint()
    {
        $_GET['foo'] = 'bar';
        $_POST['dog'] = 'cat';
        $this->app->setupPrint();
        $this->assertEquals(
            $GLOBALS['request_string'],
            'foo=bar&dog=cat&print=true'
        );
    }

    /*
     * @ticket 40277
     */
    public function testSetupPrintWithMultidimensionalArray()
    {
        $_GET['foo'] = [
            '0' => [
                '0' => 'bar',
                'a' => 'hej',
            ],
            '1' => 'notMultidemensional',
            '2' => 'notMultidemensional',
        ];
        $_POST['dog'] = 'cat';
        $this->app->setupPrint();
        $this->assertEquals('foo[1]=notMultidemensional&foo[2]=notMultidemensional&dog=cat&print=true', $GLOBALS['request_string']);
    }

    public function testLoadDisplaySettingsDefault()
    {
        $this->loadUser();

        $this->app->loadDisplaySettings();

        $this->assertEquals(
            $GLOBALS['theme'],
            $GLOBALS['sugar_config']['default_theme']
        );

        $this->removeUser();
    }

    public function testLoadDisplaySettingsAuthUserTheme()
    {
        $this->loadUser();

        $_SESSION['authenticated_user_theme'] = 'Sugar';

        $this->app->loadDisplaySettings();

        $this->assertEquals(
            $GLOBALS['theme'],
            'RacerX',
            'Multiple themes are no longer supported. It should always load RacerX'
        );

        $this->removeUser();
    }

    public function testLoadDisplaySettingsUserTheme()
    {
        $this->loadUser();
        $_REQUEST['usertheme'] = (string)SugarThemeRegistry::getDefault();

        $this->app->loadDisplaySettings();

        global $sugar_config;
        $disabledThemes = !empty($sugar_config['disabled_themes']) ? $sugar_config['disabled_themes'] : [];
        if (is_string($disabledThemes)) {
            $disabledThemes = [$disabledThemes];
        }
        $expectedTheme = !in_array($GLOBALS['theme'], $disabledThemes) ? $GLOBALS['theme'] : 'RacerX';

        $this->assertEquals(
            $expectedTheme,
            $_REQUEST['usertheme']
        );

        $this->removeUser();
    }

    public function testLoadGlobals()
    {
        $this->app->controller =
            ControllerFactory::getController($this->app->default_module);
        $this->app->loadGlobals();

        $this->assertEquals($GLOBALS['currentModule'], $this->app->default_module);
        $this->assertEquals($_REQUEST['module'], $this->app->default_module);
        $this->assertEquals($_REQUEST['action'], $this->app->default_action);
    }

    /**
     * @ticket 33283
     */
    public function testCheckDatabaseVersion()
    {
        $sugar_version = null;
        $sugar_db_version = null;
        if (isset($GLOBALS['sugar_db_version'])) {
            $old_sugar_db_version = $GLOBALS['sugar_db_version'];
        }
        if (isset($GLOBALS['sugar_version'])) {
            $old_sugar_version = $GLOBALS['sugar_version'];
        }
        include 'sugar_version.php';
        $GLOBALS['sugar_version'] = $sugar_version;

        // first test a valid value
        $GLOBALS['sugar_db_version'] = $sugar_db_version;
        $this->assertTrue($this->app->checkDatabaseVersion(false));

        $GLOBALS['sugar_db_version'] = '1.1.1';
        // then test to see if we pull against the cache the valid value
        $this->assertTrue($this->app->checkDatabaseVersion(false));

        // now retest to be sure we actually do the check again
        sugar_cache_put('checkDatabaseVersion_row_count', 0);
        $this->assertFalse($this->app->checkDatabaseVersion(false));

        if (isset($old_sugar_db_version)) {
            $GLOBALS['sugar_db_version'] = $old_sugar_db_version;
        }
        if (isset($old_sugar_version)) {
            $GLOBALS['sugar_version'] = $old_sugar_version;
        }
    }

    public function testLoadLanguages()
    {
        $this->app->controller->module = 'Contacts';
        $this->app->loadLanguages();
        //since there is a logged in user, the welcome screen should not be empty
        $this->assertEmpty($GLOBALS['app_strings']['NTC_WELCOME'], 'Testing that Welcome message is not empty');
        $this->assertNotEmpty($GLOBALS['app_strings'], 'App Strings is not empty.');
        $this->assertNotEmpty($GLOBALS['app_list_strings'], 'App List Strings is not empty.');
        $this->assertNotEmpty($GLOBALS['mod_strings'], 'Mod Strings is not empty.');
    }

    public function testCheckHTTPRefererReturnsTrueIfRefererNotSet()
    {
        $_SERVER['HTTP_REFERER'] = '';
        $_SERVER['SERVER_NAME'] = 'dog';
        $this->app->controller->action = 'index';

        $this->assertTrue($this->app->checkHTTPReferer(false));
    }

    /**
     * @ticket 39691
     */
    public function testCheckHTTPRefererReturnsTrueIfRefererIsLocalhost()
    {
        $_SERVER['HTTP_REFERER'] = 'http://localhost';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $this->app->controller->action = 'poo';

        $this->assertTrue($this->app->checkHTTPReferer(false));

        $_SERVER['HTTP_REFERER'] = 'http://127.0.0.1';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $this->app->controller->action = 'poo';

        $this->assertTrue($this->app->checkHTTPReferer(false));

        $_SERVER['HTTP_REFERER'] = 'http://localhost';
        $_SERVER['SERVER_NAME'] = '127.0.0.1';
        $this->app->controller->action = 'poo';

        $this->assertTrue($this->app->checkHTTPReferer(false));

        $_SERVER['HTTP_REFERER'] = 'http://127.0.0.1';
        $_SERVER['SERVER_NAME'] = '127.0.0.1';
        $this->app->controller->action = 'poo';

        $this->assertTrue($this->app->checkHTTPReferer(false));
    }

    public function testCheckHTTPRefererReturnsTrueIfRefererIsServerName()
    {
        $_SERVER['HTTP_REFERER'] = 'http://dog';
        $_SERVER['SERVER_NAME'] = 'dog';
        $this->app->controller->action = 'index';

        $this->assertTrue($this->app->checkHTTPReferer(false));
    }

    public function testCheckHTTPRefererReturnsTrueIfRefererIsInWhitelist()
    {
        $_SERVER['HTTP_REFERER'] = 'http://dog';
        $_SERVER['SERVER_NAME'] = 'cat';
        $this->app->controller->action = 'index';

        $GLOBALS['sugar_config']['http_referer']['list'][] = 'http://dog';

        $this->assertTrue($this->app->checkHTTPReferer(false));
    }

    public function testCheckHTTPRefererReturnsFalseIfRefererIsNotInWhitelist()
    {
        $_SERVER['HTTP_REFERER'] = 'http://dog';
        $_SERVER['SERVER_NAME'] = 'cat';
        $this->app->controller->action = 'poo';

        $GLOBALS['sugar_config']['http_referer']['list'] = [];

        $this->assertFalse($this->app->checkHTTPReferer(false));
    }

    public function testCheckHTTPRefererReturnsTrueIfRefererIsNotInWhitelistButActionIs()
    {
        $_SERVER['HTTP_REFERER'] = 'http://dog';
        $_SERVER['SERVER_NAME'] = 'cat';
        $this->app->controller->action = 'index';

        $this->assertTrue($this->app->checkHTTPReferer(false));
    }

    public function testCheckHTTPRefererReturnsTrueIfRefererIsNotInWhitelistButActionIsInConfig()
    {
        $_SERVER['HTTP_REFERER'] = 'http://dog';
        $_SERVER['SERVER_NAME'] = 'cat';
        $this->app->controller->action = 'poo';

        $GLOBALS['sugar_config']['http_referer']['actions'][] = 'poo';
        $this->assertTrue($this->app->checkHTTPReferer(false));
    }

    public function testCheckHTTPRefererReturnsTrueIfActionIsInCustomConfig()
    {
        $_SERVER['HTTP_REFERER'] = 'http://dog';
        $_SERVER['SERVER_NAME'] = 'cat';
        $this->app->controller->action = 'custom';
        $this->assertTrue($this->app->checkHTTPReferer(false));
    }

    /**
     * @bug 50302
     */
    public function testWhitelistDefaults()
    {
        $_SERVER['HTTP_REFERER'] = 'http://dog';
        $_SERVER['SERVER_NAME'] = 'cat';
        $GLOBALS['sugar_config']['http_referer']['actions'] = ['poo'];
        $this->app->controller->action = 'oauth';
        $this->assertTrue($this->app->checkHTTPReferer(false));
        $this->app->controller->action = 'index';
        $this->assertTrue($this->app->checkHTTPReferer(false));
        $this->app->controller->action = 'save';
        $this->assertFalse($this->app->checkHTTPReferer(false));
    }

    /**
     * @group Login
     */
    public function testGetAuthenticatedUrl_DefaultShouldBeSidecar()
    {
        $appReflection = new ReflectionClass('SugarApplication');
        $method = $appReflection->getMethod('getAuthenticatedHomeUrl');
        $method->setAccessible(true);

        $url = $method->invoke($this->app);

        $this->assertStringContainsString('index.php?action=sidecar#Home', $url);
    }

    /**
     * @group Login
     */
    public function testGetAuthenticatedUrl_AllowsDisablingOfSidecarWithUrlParameter()
    {
        $appReflection = new ReflectionClass('SugarApplication');
        $method = $appReflection->getMethod('getAuthenticatedHomeUrl');
        $method->setAccessible(true);

        $_GET['sidecar'] = '0';

        $url = $method->invoke($this->app);

        $this->assertStringContainsString('index.php?module=Home&action=index', $url);
    }

    /**
     * @dataProvider providerGetLoginRedirect
     */
    public function testGetLoginRedirect($add_empty, $post_data, $result_query)
    {
        $appReflection = new ReflectionClass('SugarApplication');
        $method = $appReflection->getMethod('getLoginRedirect');
        $method->setAccessible(true);

        $_POST = $post_data;
        $url = $method->invoke($this->app, $add_empty);

        $this->assertStringContainsString($result_query, $url);
    }

    public static function providerGetLoginRedirect()
    {
        return [
            [
                'add_empty' => true,
                'post_data' => [
                    'login_module' => 'foo',
                    'login_action' => 'bar',
                ],
                'result_query' => 'index.php?module=foo&action=bar',
            ],
            [
                'add_empty' => true,
                'post_data' => [
                    'login_module' => 'foo',
                    'login_action' => '',
                ],
                'result_query' => 'index.php?module=foo&action=',
            ],
            [
                'add_empty' => false,
                'post_data' => [
                    'login_module' => 'foo',
                    'login_empty_value' => '',
                    'login_zero_value' => '0',
                ],
                'result_query' => 'index.php?module=foo&zero_value=0',
            ],
        ];
    }

    /**
     * @dataProvider providerTestCreateLoginVars
     */
    public function testCreateLoginVars(array $request, $url, SugarController $controller = null)
    {
        $app = $this->getMockBuilder('SugarApplication')
            ->disableOriginalConstructor()
            ->setMethods(['getRequestVars'])
            ->getMock();

        $app->expects($this->once())
            ->method('getRequestVars')
            ->will($this->returnValue($request));

        if ($controller) {
            SugarTestReflection::setProtectedValue($app, 'controller', $controller);
        }

        $this->assertSame($url, $app->createLoginVars());
    }

    public function providerTestCreateLoginVars()
    {
        return [
            [
                [],
                '',
            ],
            [
                [
                    'csrf_token' => '123456',
                ],
                '',
            ],
            [
                [
                    'foo' => 'bar',
                ],
                '&login_foo=bar',
            ],
            [
                [
                    'foo' => 'bar',
                    'more' => 'beer',
                ],
                '&login_foo=bar&login_more=beer',
            ],
            [
                [
                    'foo' => 'bar',
                    'mobile' => '1',
                    'more' => 'beer',
                ],
                '&login_foo=bar&login_mobile=1&login_more=beer&mobile=1',
            ],
            [
                [
                    'foo' => 'bar',
                    'no_saml' => '1',
                    'more' => 'beer',
                ],
                '&login_foo=bar&login_no_saml=1&login_more=beer&no_saml=1',
            ],
            [
                [
                    'foo' => 'bar',
                    'csrf_token' => '123456',
                    'more' => 'beer',
                ],
                '&login_foo=bar&login_more=beer',
            ],
            [
                [
                    'foo' => 'bar',
                    'csrf_token' => '123456',
                    'more' => 'beer',
                ],
                '&login_foo=bar&login_more=beer',
                $this->createControllerMock(),
            ],
            [
                [
                    'foo' => 'bar',
                    'csrf_token' => '123456',
                    'more' => 'beer',
                ],
                '&login_foo=override&login_more=beer',
                $this->createControllerMock([
                    'foo' => 'override',
                ]),
            ],
            [
                [
                    'foo' => 'bar',
                    'csrf_token' => '123456',
                    'mobile' => '1',
                    'more' => 'beer',
                    'no_saml' => '1',
                ],
                '&login_foo=override&login_mobile=1&login_more=beer&login_no_saml=false&mobile=1&no_saml=1',
                $this->createControllerMock([
                    'foo' => 'override',
                    'no_saml' => 'false',
                ]),
            ],
        ];
    }

    public function testHandleAccessControl()
    {
        $this->loadUser();

        $app = $this->getMockBuilder('SugarApplication')
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequestVars'])
            ->getMock();
        $app->controller = ControllerFactory::getController('Users');

        // this can cause Warning (PHP < 8.2) or TypeError (PHP >= 8.2):
        // Undefined global variable $modInvisListActivities
        unset($GLOBALS['modInvisListActivities']);
        $GLOBALS['moduleList'][] = 'Activities';
        $app->handleAccessControl();
        $this->assertTrue($app->controller->hasAccess);

        // this can cause Warning (PHP < 8.2) or TypeError (PHP >= 8.2):
        // in_array() expects parameter 2 to be array, null given
        $GLOBALS['modInvisListActivities'] = null;
        $GLOBALS['moduleList'][] = 'Activities';
        $app->handleAccessControl();
        $this->assertTrue($app->controller->hasAccess);

        // check that the behavior was not changed
        $GLOBALS['modInvisListActivities'] = ['Users'];
        $GLOBALS['moduleList'][] = 'Activities';
        $app->handleAccessControl();
        $this->assertFalse($app->controller->hasAccess);

        $this->removeUser();
    }

    /**
     * Create SugarController mock with given public property values
     * @param array $properties Key/value pairs to set
     * @return SugarController
     */
    protected function createControllerMock(array $properties = [])
    {
        $controller = $this->createMock('SugarController');
        foreach ($properties as $property => $value) {
            $controller->$property = $value;
        }
        return $controller;
    }

    public function testHandleAccessControlModListHeaderCase()
    {
        $this->loadUser();

        // Setting up the environment
        $GLOBALS['modListHeader'] = ['ModuleA', 'ModuleB'];
        $_POST['action'] = 'SubpanelCreates';
        $_POST['target_module'] = 'ModuleC'; // Module not in $GLOBALS['modListHeader']

        $app = $this->getMockBuilder('SugarApplication')
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequestVars'])
            ->getMock();
        $app->controller = ControllerFactory::getController('Users');

        // Call the handleAccessControl method
        $app->handleAccessControl();

        // Assert that access is false
        $this->assertFalse($app->controller->hasAccess);

        // Resetting the environment
        unset($GLOBALS['modListHeader']);
        unset($_POST['action']);
        unset($_POST['target_module']);

        $this->removeUser();
    }

    public function testHandleAccessControlModListHeaderCaseTrue()
    {
        $this->loadUser();

        // Setting up the environment
        $GLOBALS['modListHeader'] = ['ModuleA', 'ModuleB', 'ModuleC'];
        $_POST['action'] = 'SubpanelCreates';
        $_POST['target_module'] = 'ModuleC'; // Module in $GLOBALS['modListHeader']

        $app = $this->getMockBuilder('SugarApplication')
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequestVars'])
            ->getMock();
        $app->controller = ControllerFactory::getController('Users');

        // Call the handleAccessControl method
        $app->handleAccessControl();

        // Assert that access is not false
        $this->assertNotFalse($app->controller->hasAccess);

        // Resetting the environment
        unset($GLOBALS['modListHeader']);
        unset($_POST['action']);
        unset($_POST['target_module']);

        $this->removeUser();
    }
}
