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


use Sugarcrm\Sugarcrm\ProcessManager;
use PHPUnit\Framework\TestCase;

/**
 * Unit test class to cover ACL testing for SugarBPM Apis
 */
class PMSEEngineApiActTest extends TestCase
{
    /**
     * @var PMSEEngineApi
     */
    private $PMSEEngineApi;

    /**
     * @var RestService
     */
    private $api;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');

        $this->PMSEEngineApi = ProcessManager\Factory::getPMSEObject('PMSEEngineApi');
        $this->api = new RestService();
        $this->api->getRequest()->setRoute(['acl' => 'adminOrDev']);
    }

    protected function tearDown(): void
    {
        SugarTestACLUtilities::tearDown();
        SugarTestHelper::tearDown();
    }

    public function testReactivateFlows()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEEngineApi->reactivateFlows($this->api, ['module' => 'pmse_Inbox']);
    }

    public function testReassignFlows()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEEngineApi->reassignFlows($this->api, ['module' => 'pmse_Inbox']);
    }

    public function testGetReassignFlows()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEEngineApi->getReassignFlows($this->api, ['module' => 'pmse_Inbox']);
    }

    public function testSelectCase()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEEngineApi->selectCase($this->api, ['module' => 'pmse_Inbox']);
    }

    public function testCancelCase()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEEngineApi->cancelCase($this->api, ['module' => 'pmse_Inbox']);
    }

    public function testGetUnattendedCases()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEEngineApi->getUnattendedCases($this->api, ['module' => 'pmse_Inbox']);
    }

    public function testGetSettingsEngine()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEEngineApi->getSettingsEngine($this->api, ['module' => 'pmse_Inbox']);
    }

    public function testPutSettingsEngine()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEEngineApi->putSettingsEngine($this->api, ['module' => 'pmse_Inbox']);
    }

    /**
     * Check if valid user is allowed to pass ACL access
     */
    public function testGetSettingsEngineValidUser()
    {
        $GLOBALS['current_user']->is_admin = 1;
        $ret = $this->PMSEEngineApi->getSettingsEngine($this->api, ['module' => 'pmse_Inbox']);
        $this->assertTrue(is_array($ret), 'ACL access test failed for getSettingsEngine');
    }
}
