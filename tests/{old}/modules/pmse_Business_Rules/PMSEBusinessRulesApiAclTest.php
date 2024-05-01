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
class PMSEBusinessRulesApiAclTest extends TestCase
{
    /**
     * @var PMSEBusinessRules
     */
    private $PMSEBusinessRules;

    /**
     * @var RestService
     */
    private $api;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');

        $this->PMSEBusinessRules = ProcessManager\Factory::getPMSEObject('PMSEBusinessRules');
        $this->api = new RestService();
        $this->api->getRequest()->setRoute(['acl' => 'view']);
    }

    protected function tearDown(): void
    {
        SugarTestACLUtilities::tearDown();
        SugarTestHelper::tearDown();
    }

    public function testBusinessRuleDownload()
    {
        $this->expectException(SugarApiExceptionMissingParameter::class);
        $this->PMSEBusinessRules->businessRuleDownload(
            $this->api,
            ['module' => 'pmse_Business_Rules']
        );
    }

    public function testBusinessRulesImport()
    {
        $this->expectException(SugarApiExceptionMissingParameter::class);
        $this->PMSEBusinessRules->businessRulesImport($this->api, ['module' => 'pmse_Business_Rules']);
    }

    /**
     * Check if valid user is allowed to pass ACL access
     */
    public function testBusinessRuleDownloadValidUser()
    {
        $GLOBALS['current_user']->is_admin = 1;

        $pmseBusinessRuleExporter = $this->getMockBuilder('PMSEBusinessRuleExporter')
            ->setMethods(['exportProject'])
            ->getMock();
        $pmseBusinessRuleExporter
            ->expects($this->any())
            ->method('exportProject')
            ->will($this->returnValue('testPassed'));

        $pmseBusinessRulesApi = $this->getMockBuilder('PMSEBusinessRules')
            ->setMethods(['getPMSEBusinessRuleExporter'])
            ->getMock();
        $pmseBusinessRulesApi
            ->expects($this->any())
            ->method('getPMSEBusinessRuleExporter')
            ->will($this->returnValue($pmseBusinessRuleExporter));

        $bean = $this->getMockBuilder('pmse_Business_Rules')->getMock();
        $bean->id = 'dummy';
        $bean->method('ACLAccess')
            ->will($this->returnValue(true));
        BeanFactory::registerBean($bean);

        $ret = $pmseBusinessRulesApi->businessRuleDownload(
            $this->api,
            ['module' => 'pmse_Business_Rules', 'record' => 'dummy']
        );
        $this->assertEquals($ret, 'testPassed', 'ACL access test failed for businessRuleDownload');
        BeanFactory::unregisterBean($bean);
    }
}
