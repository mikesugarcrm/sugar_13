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

class MetaDataManagerPortalTest extends TestCase
{
    protected $mm;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('current_user', [true, true]);
        $this->mm = MetaDataManager::getManager('portal');
    }

    protected function tearDown(): void
    {
        $dir = 'custom/modules/Opportunities/clients/portal';
        if (is_dir($dir)) {
            rmdir($dir);
        }
        MetaDataFiles::clearModuleClientCache();
        MetaDataManager::resetManagers();
        SugarTestHelper::tearDown();
    }

    /**
     * @covers \MetaDataManagerPortal::getModules
     */
    public function testGetModules()
    {
        $enabledModules = [
            'Bugs',
            'Cases',
            'Contacts',
            'Emails',
            'Home',
            'KBContents',
            'Messages',
            'Notes',
            'Users',
            'Filters',
            'Dashboards',
        ];

        $response = SugarTestReflection::callProtectedMethod($this->mm, 'getModules');

        $this->assertNotContains('Meetings', $response);

        sort($response);
        sort($enabledModules);
        $this->assertEquals($response, $enabledModules);
    }

    /**
     * @covers \MetaDataManagerPortal::getModules
     */
    public function testGetModulesIncludesCustomOpportunities()
    {
        mkdir_recursive('custom/modules/Opportunities/clients/portal');

        $response = SugarTestReflection::callProtectedMethod($this->mm, 'getModules');
        $this->assertContains('Opportunities', $response);
    }
}
