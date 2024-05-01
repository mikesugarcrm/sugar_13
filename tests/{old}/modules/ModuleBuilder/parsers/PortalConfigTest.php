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

class PortalConfigParserTest extends TestCase
{
    /**
     * @var mixed|\User
     */
    public $user;
    /**
     * @var \ACLRole|mixed
     */
    public $role;
    private $requestVars = [
        'appName' => 'testApp',
        'serverUrl' => 'testURL',
        'maxQueryResult' => '5',
    ];

    protected function setUp(): void
    {
        global $app_list_strings;
        $app_list_strings = return_app_list_strings_language($GLOBALS['current_language']);
        SugarTestHelper::setUp('mod_strings', ['ModuleBuilder']);
    }

    protected function tearDown(): void
    {
        if (isset($this->user->id)) {
            $GLOBALS['db']->query("DELETE FROM users WHERE id = '{$this->user->id}'");
            if ($GLOBALS['db']->tableExists('users_cstm')) {
                $GLOBALS['db']->query("DELETE FROM users_cstm WHERE id_c = '{$this->user->id}'");
            }
        }
        SugarTestHelper::tearDown();
    }

    public function test_PortalConfigParserSetUpConfig()
    {
        $admin = $this->getMockBuilder('Administration')
            ->disableOriginalConstructor()->getMock();
        $admin->expects($this->atLeastOnce())
            ->method('saveSetting')
            ->with(
                $this->equalTo('portal'),
                $this->anything(),
                $this->anything(),
                $this->equalTo('support')
            )
            ->willReturn(true);

        $parser = $this->getMockBuilder('ParserModifyPortalConfig')
            ->setMethods(['getAdministrationBean', 'refreshCache'])
            ->getMock();

        $parser->expects($this->once())
            ->method('refreshCache');

        $parser->expects($this->atLeastOnce())
            ->method('getAdministrationBean')
            ->willReturn($admin);

        $parser->setUpPortal($this->requestVars);
    }

    public function test_PortalConfigUnsetConfig()
    {
        $admin = $this->getMockBuilder('Administration')
            ->disableOriginalConstructor()->getMock();

        $admin->expects($this->atLeastOnce())
            ->method('saveSetting')
            ->with(
                $this->equalTo('portal'),
                $this->anything(),
                $this->anything(),
                $this->equalTo('support')
            )
            ->willReturn(true);

        $parser = $this->getMockBuilder('ParserModifyPortalConfig')
            ->setMethods(['getAdministrationBean', 'removeOAuthForPortalUser'])
            ->getMock();

        $parser->expects($this->atLeastOnce())
            ->method('getAdministrationBean')
            ->willReturn($admin);

        $parser->expects($this->Once())
            ->method('removeOAuthForPortalUser');

        $parser->unsetPortal();
    }

    public function test_PortalConfigCreateUser()
    {
        $parser = new ParserModifyPortalConfig();
        $this->user = $parser->getPortalUser();
        $this->assertNotEquals($this->user->id, '');
        $oUserId = $this->user->id;
        $this->user = $parser->getPortalUser();
        $this->assertEquals($this->user->id, $oUserId);
    }

    public function test_RemoveOAuthForPortalUser()
    {
        $parser = new ParserModifyPortalConfig();
        $this->user = $parser->getPortalUser();
        $result = $GLOBALS['db']->getOne("SELECT name FROM oauth_consumer WHERE c_key='support_portal'");
        $this->assertNotNull($result, 'getPortalUser should create an OAuth consumer');
        $parser->removeOAuthForPortalUser();
        $result = $GLOBALS['db']->getOne("SELECT name FROM oauth_consumer WHERE c_key='support_portal'");
        $this->assertFalse($result, 'removeOAuthForPortalUser should remove OAuth consumer');
    }

    public function test_PortalConfigCreateRole()
    {
        $parser = new ParserModifyPortalConfig();

        $this->role = $parser->getPortalACLRole();
        $this->assertNotEquals($this->role->id, '');

        $oRoleId = $this->role->id;

        $this->role = $parser->getPortalACLRole();

        $this->assertEquals($this->role->id, $oRoleId);
    }
}
