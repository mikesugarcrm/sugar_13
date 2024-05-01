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

class BeanAssigneeNotificationTest extends TestCase
{
    private $siteUrl;

    protected function setUp(): void
    {
        global $current_user, $sugar_config;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $this->siteUrl = $sugar_config['site_url'];
    }

    protected function tearDown(): void
    {
        SugarTestCampaignUtilities::removeAllCreatedCampaigns();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testAssigneeForBWCModule()
    {
        $bean = SugarTestCampaignUtilities::createCampaign('', 'CampaignMock');
        $template = $bean->getNotificationEmailTemplate(true);
        $url = $template->VARS['URL'];

        // check if the URL points to the proper instance
        $this->assertStringStartsWith($this->siteUrl, $url);

        // analyze URL fragment as URL inside URL
        $fragment = parse_url($url, PHP_URL_FRAGMENT);
        $components = parse_url($fragment);

        $this->assertStringStartsWith('bwc/', $components['path']);

        $expectedQuery = [
            'module' => $bean->module_dir,
            'action' => 'DetailView',
            'record' => $bean->id,
        ];

        parse_str($components['query'], $query);


        $this->assertArrayHasKey('module', $query);
        $this->assertArrayHasKey('action', $query);
        $this->assertArrayHasKey('record', $query);

        $this->assertEquals($query['module'], $expectedQuery['module']);
        $this->assertEquals($query['action'], $expectedQuery['action']);
        $this->assertEquals($query['record'], $expectedQuery['record']);
    }

    public function testAssigneeForNonBWCModule()
    {
        $bean = BeanFactory::newBean('Contacts');
        $templateName = SugarTestReflection::callProtectedMethod($bean, 'getTemplateNameForNotificationEmail');
        $template = SugarTestReflection::callProtectedMethod(
            $bean,
            'createNotificationEmailTemplate',
            [$templateName]
        );

        $url = $template->VARS['URL'];

        // check if the URL points to the proper instance
        $this->assertStringStartsWith($this->siteUrl, $url);

        $fragment = parse_url($url, PHP_URL_FRAGMENT);
        $this->assertEquals($bean->module_name . '/' . $bean->id, $fragment);
    }
}
