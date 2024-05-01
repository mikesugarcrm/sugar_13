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
 * Class DRICustomerJourneyConfigApiTest
 * @coversDefaultClass \DRICustomerJourneyConfigApi
 */
class DRICustomerJourneyConfigApiTest extends TestCase
{
    /**
     * @var \ServiceBase|MockObject
     */
    private $apiService;

    /**
     * @var \AdministrationApi|MockObject
     */
    private $api;

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
        $this->cjTestHelper = new SugarTestCJHelper();
        $this->apiService = $this->createMock(\ServiceBase::class);
        $this->api = $this->createPartialMock(
            \DRICustomerJourneyConfigApi::class,
            ['requireArgs']
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        $this->cjTestHelper->tearDown();
    }

    /**
     * @covers saveConfigureModules
     */
    public function testSaveConfigureModules(): void
    {
        $args = [
            'module' => 'DRI_Workflow_Templates',
            'record' => 'testTemplate',
            'enabled_modules' => '^Accounts^,^Cases^,^Opportunities^',
            'recordview_display_settings' => '123456',
        ];

        $this->cjTestHelper->createBean('DRI_Workflow_Templates', [
            'url' => 'https://www.google.com',
            'headers' => 'Accept-Language: en-US,en;q=0.5',
        ]);

        $response = $this->api->saveConfigureModules($this->apiService, $args);
        $this->assertEquals($response, 1);
    }
}
