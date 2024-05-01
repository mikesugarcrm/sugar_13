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

abstract class SOAPTestCase extends TestCase
{
    protected static $user;
    protected $soapClient;
    protected $soapClientNoWsdl;
    protected $sessionId;
    protected $soapURL;

    public static function setUpBeforeClass(): void
    {
        self::$user = BeanFactory::retrieveBean('Users', '1');
        $GLOBALS['current_user'] = self::$user;
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        unset($GLOBALS['current_user']);
        $GLOBALS['db']->commit();
    }

    /**
     * Create test user
     */
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        $this->soapURL = $this->soapURL ?? $GLOBALS['sugar_config']['site_url']
        . '/service/v4_1/soap.php';
        // $this->_soapURL .= '?XDEBUG_SESSION_START=phpstorm-xdebug';

        $params = [
            'soap_version' => SOAP_1_1,
            'trace' => 0,
            'exceptions' => 1,
        ];
        $this->soapClient = new \SoapClient($this->soapURL . '?wsdl', $params);

        $params = [
            'soap_version' => SOAP_1_1,
            'trace' => 0,
            'exceptions' => 1,
            'uri' => $this->soapURL,
            'location' => $this->soapURL,
        ];
        $this->soapClientNoWsdl = new \SoapClient(null, $params);
        $GLOBALS['db']->commit();
    }

    /**
     * Remove anything that was used during this test
     */
    protected function tearDown(): void
    {
        $this->sessionId = '';

        SugarTestHelper::tearDown();
        $GLOBALS['db']->commit();
    }

    protected function login()
    {
        $GLOBALS['db']->commit();
        $result = $this->soapClient->login(
            [
                'user_name' => 'admin',
                'password' => md5('asdf'),
                'version' => '.01',
            ],
            'SoapTest',
            []
        );
        $this->sessionId = $result->id;
        return $result;
    }

    protected function loginLegacy()
    {
        $GLOBALS['db']->commit();
        $result = $this->soapClient->login(
            [
                'user_name' => 'admin',
                'password' => md5('asdf'),
                'version' => '.01',
            ],
            'SoapTest'
        );
        $this->sessionId = $result->id;
        return $result;
    }

    /**
     * Remove user created for test
     */
    protected function tearDownTestUser()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }
}
