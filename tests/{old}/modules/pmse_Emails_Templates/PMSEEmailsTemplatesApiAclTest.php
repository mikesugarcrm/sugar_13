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
class PMSEEmailsTemplatesApiAclTest extends TestCase
{
    /**
     * @var PMSEEmailsTemplates
     */
    private $PMSEEmailsTemplates;

    /**
     * @var RestService
     */
    private $api;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');

        $this->PMSEEmailsTemplates = ProcessManager\Factory::getPMSEObject('PMSEEmailsTemplates');
        $this->api = new RestService();
        $this->api->getRequest()->setRoute(['acl' => 'view']);
    }

    protected function tearDown(): void
    {
        SugarTestACLUtilities::tearDown();
        SugarTestHelper::tearDown();
    }

    public function testEmailTemplateDownload()
    {
        $this->expectException(SugarApiExceptionMissingParameter::class);
        $this->PMSEEmailsTemplates->emailTemplateDownload(
            $this->api,
            ['module' => 'pmse_Emails_Templates']
        );
    }

    public function testEmailTemplateSuccessfullDownload()
    {
        $bean = $this->getMockBuilder('pmse_Emails_Templates')->getMock();
        $bean->id = 'dummy';
        $bean->method('ACLAccess')
            ->will($this->returnValue(true));
        BeanFactory::registerBean($bean);

        $pmseEmailTemplateExporter = $this->getMockBuilder('PMSEEmailTemplateExporter')
            ->setMethods(['exportProject'])
            ->getMock();
        $pmseEmailTemplateExporter
            ->expects($this->any())
            ->method('exportProject')
            ->will($this->returnValue('testPassed'));

        $pmseEmailsTemplatesApi = $this->getMockBuilder('PMSEEmailsTemplates')
            ->setMethods(['getEmailTemplateExporter'])
            ->getMock();
        $pmseEmailsTemplatesApi
            ->expects($this->any())
            ->method('getEmailTemplateExporter')
            ->will($this->returnValue($pmseEmailTemplateExporter));

        $ret = $pmseEmailsTemplatesApi->emailTemplateDownload(
            $this->api,
            ['module' => 'pmse_Emails_Templates', 'record' => 'dummy']
        );
        $this->assertEquals($ret, 'testPassed', 'ACL access test failed for emailTemplateDownload');
        BeanFactory::unregisterBean($bean);
    }

    public function testFindVariables()
    {
        $ret = $this->PMSEEmailsTemplates->findVariables(
            $this->api,
            [
                'module' => 'pmse_Emails_Templates',
                'module_list' => 'Accounts',
                'order_by' => 'name',
                'base_module' => 'Accounts',
            ]
        );
        $this->assertArrayHasKey('next_offset', $ret);
        $this->assertArrayHasKey('records', $ret);
    }

    public function testRetrieveRelatedBeans()
    {
        $ret = $this->PMSEEmailsTemplates->retrieveRelatedBeans(
            $this->api,
            [
                'module' => 'pmse_Emails_Templates',
                'module_list' => 'Accounts',
                'order_by' => 'name',
                'base_module' => 'Accounts',
            ]
        );
        $this->assertArrayHasKey('search', $ret);
        $this->assertArrayHasKey('success', $ret);
        $this->assertArrayHasKey('result', $ret);
        $this->assertEquals('Accounts', $ret['search']);
        $this->assertEquals(true, $ret['success']);
    }

    public function testEmailTemplatesImport()
    {
        $this->expectException(SugarApiExceptionMissingParameter::class);
        $this->PMSEEmailsTemplates->emailTemplatesImport(
            $this->api,
            ['module' => 'pmse_Emails_Templates']
        );
    }

    /**
     * Check if valid user is allowed to pass ACL access
     */
    public function testFindVariablesValidUser()
    {
        $GLOBALS['current_user']->is_admin = 1;
        $ret = $this->PMSEEmailsTemplates->findVariables(
            $this->api,
            [
                'module' => 'pmse_Emails_Templates',
                'module_list' => 'Accounts',
                'order_by' => 'name',
                'base_module' => 'Accounts',
            ]
        );
        $this->assertTrue(is_array($ret), 'ACL access test failed for findVariables');
    }
}
