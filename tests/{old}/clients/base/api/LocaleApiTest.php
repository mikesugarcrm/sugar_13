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
 * @group ApiTests
 */
class LocaleApiTest extends TestCase
{
    /**
     * @var LocaleApi
     */
    protected $api;

    /**
     * @var RestService
     */
    protected $serviceMock;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp(): void
    {
        $this->api = new LocaleApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testRetrieveLocaleOptions()
    {
        $result = $this->api->localeOptions($this->serviceMock, []);

        $this->assertNotEmpty($result);
        $this->assertIsArray($result);

        $fields = ['timepref', 'datepref', 'default_locale_name_format', 'timezone'];

        foreach ($fields as $field) {
            $this->assertArrayHasKey($field, $result);
            $this->assertIsArray($result[$field]);
        }
    }
}
