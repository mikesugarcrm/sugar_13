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

require_once 'modules/ACLActions/actiondefs.php';

class TeamBasedACLConfiguratorTest extends TestCase
{
    /**
     * @var TeamBasedACLConfigurator
     */
    protected $tbaConfig;

    /**
     * @var boolean
     */
    protected $globalTBA;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var string
     */
    protected $module = 'Accounts';

    /**
     * @var ACLRole
     */
    protected $role;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        $this->tbaConfig = $this->createPartialMock(
            'TeamBasedACLConfigurator',
            ['applyTBA', 'restoreTBA', 'fallbackTBA', 'applyFallback']
        );
        $this->globalTBA = $GLOBALS['sugar_config'][TeamBasedACLConfigurator::CONFIG_KEY]['enabled'];

        $this->tbaConfig->setGlobal(true);
        $this->tbaConfig->setForModule($this->module, true);
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $this->role = new ACLRole();
        $this->role->save();
    }

    protected function tearDown(): void
    {
        $this->role->mark_deleted($this->role->id);
        $this->tbaConfig->setGlobal($this->globalTBA);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    /**
     * Disabled globally should affect modules as well.
     */
    public function testGlobalConfig()
    {
        $this->tbaConfig->setGlobal(false);

        $this->assertFalse($this->tbaConfig->isEnabledGlobally());
        $this->assertFalse($GLOBALS['sugar_config'][TeamBasedACLConfigurator::CONFIG_KEY]['enabled']);
        $this->assertFalse($this->tbaConfig->isEnabledForModule($this->module));

        $this->tbaConfig->setGlobal(true);
        $this->assertTrue($this->tbaConfig->isEnabledGlobally());
        $this->assertTrue($GLOBALS['sugar_config'][TeamBasedACLConfigurator::CONFIG_KEY]['enabled']);
    }

    /**
     * Test module level.
     */
    public function testModuleConfig()
    {
        $this->tbaConfig->setForModule($this->module, false);
        $this->assertFalse($this->tbaConfig->isEnabledForModule($this->module));

        $this->tbaConfig->setForModule($this->module, true);
        $this->assertTrue($this->tbaConfig->isEnabledForModule($this->module));

        $this->tbaConfig->setGlobal(false);
        $this->assertFalse($this->tbaConfig->isEnabledForModule($this->module));
    }

    /**
     * Test module level using a set of modules.
     */
    public function testModuleListConfig()
    {
        $extraModule = 'Bugs';
        $moduleList = [$this->module, $extraModule];
        $this->tbaConfig->setForModule($extraModule, true);

        $this->tbaConfig->setForModulesList($moduleList, false);
        $this->assertFalse($this->tbaConfig->isEnabledForModule($this->module));
        $this->assertFalse($this->tbaConfig->isEnabledForModule($extraModule));

        $this->tbaConfig->setForModulesList($moduleList, true);
        $this->assertTrue($this->tbaConfig->isEnabledForModule($this->module));
        $this->assertTrue($this->tbaConfig->isEnabledForModule($extraModule));
    }

    /**
     * Test cases:
     * Fallback - should replace TBA options.
     * Restore - should restore the previous TBA options.
     * Restore changed ACL - should not restore changed ACL.
     * @dataProvider moduleProvider
     */
    public function testFallbackAndRestore($module, $field)
    {
        $this->tbaConfig = $this->createPartialMock('TeamBasedACLConfigurator', ['applyTBA']);
        $this->tbaConfig->setForModule($module, true);
        $action = 'view';
        $aclType = BeanFactory::newBean($module)->acltype;
        $aclField = new ACLField();
        $roleActions = $this->role->getRoleActions($this->role->id);
        $fallbackFieldAccess = $this->tbaConfig->getFallbackByAccess(constant('ACL_SELECTED_TEAMS_READ_WRITE'));
        $fallbackModuleAccess = $this->tbaConfig->getFallbackByAccess(constant('ACL_ALLOW_SELECTED_TEAMS'));

        $availableFields = ACLField::getAvailableFields($module, false);
        if (!isset($availableFields[$field])) {
            $this->markTestSkipped('Passed field is not valid');
        }
        // Set ACL for Module.
        $actionId = $roleActions[$module][$aclType][$action]['id'];
        $this->role->setAction($this->role->id, $actionId, ACL_ALLOW_SELECTED_TEAMS);

        // Set ACL for Field.
        $aclField->setAccessControl($module, $this->role->id, $field, ACL_SELECTED_TEAMS_READ_WRITE);

        // Fallback.
        $this->tbaConfig->setForModule($module, false);

        $actualActions = $this->role->getRoleActions($this->role->id);
        $this->assertEquals($fallbackModuleAccess, $actualActions[$module][$aclType][$action]['aclaccess']);

        $actualAclFields = $aclField->getACLFieldsByRole($this->role->id);
        $fieldKeys = array_keys($actualAclFields);
        $this->assertEquals($fallbackFieldAccess, $actualAclFields[$fieldKeys[0]]['aclaccess']);

        // Restore.
        $this->tbaConfig->setForModule($module, true);

        $actualActions = $this->role->getRoleActions($this->role->id);
        $this->assertEquals(ACL_ALLOW_SELECTED_TEAMS, $actualActions[$module][$aclType][$action]['aclaccess']);

        $actualAclFields = $aclField->getACLFieldsByRole($this->role->id);
        $fieldKeys = array_keys($actualAclFields);
        $this->assertEquals(ACL_SELECTED_TEAMS_READ_WRITE, $actualAclFields[$fieldKeys[0]]['aclaccess']);

        // Restore changed ACL
        $this->tbaConfig->setGlobal(false);

        $actualActions = $this->role->getRoleActions($this->role->id);
        $this->assertEquals($fallbackModuleAccess, $actualActions[$module][$aclType][$action]['aclaccess']);

        $actualAclFields = $aclField->getACLFieldsByRole($this->role->id);
        $fieldKeys = array_keys($actualAclFields);
        $this->assertEquals($fallbackFieldAccess, $actualAclFields[$fieldKeys[0]]['aclaccess']);

        // Change the options.
        $actionId = $roleActions[$module][$aclType][$action]['id'];
        $this->role->setAction($this->role->id, $actionId, ACL_ALLOW_DEFAULT);
        $aclField->setAccessControl($module, $this->role->id, $field, ACL_ALLOW_DEFAULT);

        $this->tbaConfig->setGlobal(true);

        $actualActions = $this->role->getRoleActions($this->role->id);
        $this->assertEquals(ACL_ALLOW_DEFAULT, $actualActions[$module][$aclType][$action]['aclaccess']);

        $actualAclFields = $aclField->getACLFieldsByRole($this->role->id);
        $fieldKeys = array_keys($actualAclFields);
        $this->assertEquals(ACL_ALLOW_DEFAULT, $actualAclFields[$fieldKeys[0]]['aclaccess']);
    }

    public function moduleProvider()
    {
        return [
            // Module, Field.
            [$this->module, 'name'],
            // Different ACL type.
            ['TrackerQueries', 'run_count'],
        ];
    }

    /**
     * Test that global enabling does not restore disabled modules.
     */
    public function testRestoreDisabledModules()
    {
        $this->tbaConfig = $this->createPartialMock('TeamBasedACLConfigurator', ['applyTBA']);
        $action = 'view';
        $roleActions = $this->role->getRoleActions($this->role->id);
        $fallbackModuleAccess = $this->tbaConfig->getFallbackByAccess(constant('ACL_ALLOW_SELECTED_TEAMS'));

        $actionId = $roleActions[$this->module]['module'][$action]['id'];
        $this->role->setAction($this->role->id, $actionId, ACL_ALLOW_SELECTED_TEAMS);

        // Fallback.
        $this->tbaConfig->setForModule($this->module, false);

        $this->tbaConfig->setGlobal(false);
        $this->tbaConfig->setGlobal(true);

        $actualActions = $this->role->getRoleActions($this->role->id);
        $this->assertEquals($fallbackModuleAccess, $actualActions[$this->module]['module'][$action]['aclaccess']);
    }

    /**
     * Test that mass disabling does not affect rest modules.
     */
    public function testFallbackDisabledModules()
    {
        $this->tbaConfig = $this->createPartialMock('TeamBasedACLConfigurator', ['applyTBA']);
        $action = 'view';
        $extraModule = 'Bugs';
        $roleActions = $this->role->getRoleActions($this->role->id);
        // make sure it is disabled to test fallback
        $this->tbaConfig->setForModule($extraModule, false);

        $fallbackModuleAccess = $this->tbaConfig->getFallbackByAccess(constant('ACL_ALLOW_SELECTED_TEAMS'));
        $this->tbaConfig->setForModule($extraModule, true);

        $actionId = $roleActions[$this->module]['module'][$action]['id'];
        $this->role->setAction($this->role->id, $actionId, ACL_ALLOW_SELECTED_TEAMS);

        $actionId = $roleActions[$extraModule]['module'][$action]['id'];
        $this->role->setAction($this->role->id, $actionId, ACL_ALLOW_SELECTED_TEAMS);

        // Fallback.
        $this->tbaConfig->setForModulesList([$extraModule], false);

        $actualActions = $this->role->getRoleActions($this->role->id);

        $this->assertEquals($fallbackModuleAccess, $actualActions[$extraModule]['module'][$action]['aclaccess']);
        $this->assertEquals(ACL_ALLOW_SELECTED_TEAMS, $actualActions[$this->module]['module'][$action]['aclaccess']);
    }

    /**
     * Data provider for testRemoveTBAValuesFromBean.
     *
     * @return array
     * @see TeamBasedACLConfiguratorTest::testRemoveTBAValuesFromBean
     */
    public static function removeTBAValuesFromBeanDataProvider()
    {
        return [
            // Versions module doesn't implement tba, so call removeAllTBAValuesFromTable only for accounts table
            [['Versions' => 'Version', 'Accounts' => 'Account',], 'accounts'],
            // Users module implements tba, but exists in alwaysEnabledModules list
            [['Accounts' => 'Account', 'Users' => 'User'], 'accounts'],
        ];
    }

    /**
     * Check that we don't call removeAllTBAValuesFromTable for modules which marked as 'always enabled'
     * or don't implement TBA
     *
     * @dataProvider RemoveTBAValuesFromBeanDataProvider
     * @covers       TeamBasedACLConfigurator::removeAllTBAValuesFromBean
     * @param array $beanList
     * @param string $expectedTableName
     */
    public function testRemoveTBAValuesFromBean($beanList, $expectedTableName)
    {
        $this->tbaConfig = $this->getMockBuilder('TeamBasedACLConfigurator')
            ->setMethods(['removeAllTBAValuesFromTable'])
            ->getMock();
        $this->tbaConfig
            ->expects($this->once())
            ->method('removeAllTBAValuesFromTable')
            ->with($expectedTableName);

        foreach ($beanList as $moduleName => $beanName) {
            $bean = $this->getMockBuilder($beanName)->setMethods(null)->getMock();
            $this->tbaConfig->removeAllTBAValuesFromBean($bean);
        }
    }
}
