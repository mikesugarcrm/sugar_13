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
class PMSEProjectApiAclTest extends TestCase
{
    /**
     * @var PMSEProjectApi
     */
    private $PMSEProjectApi;

    /**
     * @var RestService
     */
    private $api;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');

        $this->PMSEProjectApi = ProcessManager\Factory::getPMSEObject('PMSEProjectApi');
        $this->api = new RestService();
        $this->api->getRequest()->setRoute(['acl' => []]);
    }

    protected function tearDown(): void
    {
        SugarTestACLUtilities::tearDown();
        SugarTestHelper::tearDown();
    }

    public function testRetrieveCustomProject()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEProjectApi->retrieveCustomProject($this->api, ['module' => 'pmse_Project']);
    }

    public function testUpdateCustomProject()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEProjectApi->updateCustomProject($this->api, ['module' => 'pmse_Project']);
    }

    public function testGetCrmData()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEProjectApi->getCrmData($this->api, ['module' => 'pmse_Project']);
    }

    public function testPutCrmData()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEProjectApi->putCrmData($this->api, ['module' => 'pmse_Project']);
    }

    public function testGetActivityDefinition()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEProjectApi->getActivityDefinition($this->api, ['module' => 'pmse_Project']);
    }

    public function testPutActivityDefinition()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEProjectApi->putActivityDefinition($this->api, ['module' => 'pmse_Project']);
    }

    public function testGetEventDefinition()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEProjectApi->getEventDefinition($this->api, ['module' => 'pmse_Project']);
    }

    public function testPutEventDefinition()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEProjectApi->putEventDefinition($this->api, ['module' => 'pmse_Project']);
    }

    public function testGetGatewayDefinition()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEProjectApi->getGatewayDefinition($this->api, ['module' => 'pmse_Project']);
    }

    public function testPutGatewayDefinition()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEProjectApi->putGatewayDefinition($this->api, ['module' => 'pmse_Project']);
    }

    public function testVerifyRunningProcess()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSEProjectApi->verifyRunningProcess($this->api, ['module' => 'pmse_Project']);
    }
}
