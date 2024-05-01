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

/**
 * Class CJ_WebHooksTest
 * @coversDefaultClass \CJ_WebHooks
 */
class CJ_WebHooksTest extends TestCase
{
    /**
     * @var \SugarBean
     */
    private $webHook;

    /**
     * @var \SugarTestCJHelper
     */
    private $cjTestHelper;

    /**
     * @var string|false
     */
    public static function setUpBeforeClass(): void
    {
        $GLOBALS['log'] = LoggerManager::getLogger();
        $GLOBALS['current_language'] = 'en_us';
        SugarTestHelper::init();

        //No need however to add the following
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->webHook = new CJ_WebHook();
        $this->cjTestHelper = new SugarTestCJHelper();
    }

    /**
     * @covers ::send
     */
    public function testsend(): void
    {
        $mockUser = $this->cjTestHelper->createBean('Users');

        $this->cjTestHelper->setCurrentUser($mockUser);

        $testTemplate = $this->cjTestHelper->createBean('DRI_Workflow_Templates');

        $webHookValues = [
            'active' => true,
            'sort_order' => 5,
            'url' => 'https://www.google.com',
            'headers' => 'Accept-Language: en-US,en;q=0.5',
            'trigger_event' => 'before_create',
            'parent_id' => 'testTemplate',
            'parent_type' => 'DRI_Workflow_Templates',
        ];
        $this->cjTestHelper->createBean('CJ_WebHooks', $webHookValues);
        CJ_WebHook::send($testTemplate, 'before_create', []);

        $key = 'CJ_WebHook::getWebHooksByParent[DRI_Workflow_Templates][testTemplate][before_create]';
        $response = sugar_cache_retrieve($key);

        $this->assertEquals($response[0]['id'], 'testWebHook');
        $this->assertEquals($response[0]['cj_web_hooks__sort_order'], 5);
    }

    /**
     * @covers ::copyWebHooks
     */
    public function testCopyWebHooks(): void
    {
        $mockUser = $this->cjTestHelper->createBean('Users');
        $this->cjTestHelper->setCurrentUser($mockUser);

        $testTemplate = $this->cjTestHelper->createBean('DRI_Workflow_Templates');
        $testWebhook = $this->cjTestHelper->createBean('CJ_WebHooks');

        if ($testTemplate->load_relationship('web_hooks')) {
            $testTemplate->web_hooks->add($testWebhook->id);
        }

        CJ_WebHook::copyWebHooks($testTemplate, $testTemplate);

        $query = new SugarQuery();
        $query->from(BeanFactory::newBean('CJ_WebHooks'));
        $query->select(['id', 'trigger_event', 'parent_type', 'parent_id']);
        $query->where()
            ->equals('deleted', 0)
            ->notEquals('id', $testWebhook->id)
            ->equals('parent_type', $testTemplate->module_dir)
            ->equals('parent_id', $testTemplate->id);

        $results = $query->execute();

        // copied webhook should be in database
        $this->assertEquals(count($results), 1);

        foreach ($results as $result) {
            $webHook = BeanFactory::retrieveBean('CJ_WebHooks', $result['id']);

            if ($webHook) {
                $this->cjTestHelper->addBeanToDeleteAssets($webHook);
            }
        }
    }

    /**
     * @covers ::deleteWebHooks
     */
    public function testDeleteWebHooks(): void
    {
        $testTemplate = $this->cjTestHelper->createBean('DRI_Workflow_Templates');
        $testWebhook = $this->cjTestHelper->createBean('CJ_WebHooks');

        if ($testTemplate->load_relationship('web_hooks')) {
            $testTemplate->web_hooks->add($testWebhook->id);
        }

        $this->assertEquals($testWebhook->deleted, 0);

        CJ_WebHook::deleteWebHooks($testTemplate);

        // related web_hooks should have deleted flag true
        foreach ($testTemplate->web_hooks->getBeans() as $webHook) {
            $this->assertEquals($testWebhook->deleted, 1);
        }
    }

    /**
     * @covers ::mark_deleted
     */
    public function testMark_deleted(): void
    {
        $testTemplate = $this->cjTestHelper->createBean('DRI_Workflow_Templates');
        $testWebhook = $this->cjTestHelper->createBean('CJ_WebHooks');

        $key = 'CJ_WebHook::getWebHooksByParent[DRI_Workflow_Templates][testTemplate][before_create]';
        sugar_cache_put($key, 'TestCache');

        $this->assertEquals(sugar_cache_retrieve($key), 'TestCache');

        $this->webHook->parent_id = $testTemplate->id;
        $this->webHook->parent_type = 'DRI_Workflow_Templates';
        $this->webHook->trigger_event = 'before_create';
        $this->webHook->mark_deleted($testWebhook->id);

        // sugar cache should be cleared
        $this->assertEquals(sugar_cache_retrieve($key), '');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        $this->cjTestHelper->tearDown();
    }
}
