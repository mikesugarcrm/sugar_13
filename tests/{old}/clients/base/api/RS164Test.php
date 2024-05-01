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
 *  RS164: Prepare Theme Api.
 */
class RS164Test extends TestCase
{
    /**
     * @var ThemeApi
     */
    protected $api;

    /**
     * @var RestService
     */
    protected static $rest;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, false]);
        self::$rest = SugarTestRestUtilities::getRestServiceMock();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestFilterUtilities::removeAllCreatedFilters();
        SugarTestHelper::tearDown();
    }

    protected function setUp(): void
    {
        $this->api = new ThemeApi();
    }

    public function testGetCSSURLs()
    {
        $result = $this->api->getCSSURLs(
            self::$rest,
            []
        );
        $this->assertArrayHasKey('url', $result);
    }

    public function testPreviewCSS()
    {
        $this->expectOutputRegex('/padding|margin/');
        $this->api->previewCSS(
            self::$rest,
            []
        );
    }

    public function testGetCustomThemeVars()
    {
        $result = $this->api->getCustomThemeVars(
            self::$rest,
            []
        );
        $this->assertNotEmpty($result);
    }

    public function testUpdateCustomThemeException()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->api->updateCustomTheme(
            self::$rest,
            []
        );
    }

    public function testUpdateCustomTheme()
    {
        $admin = SugarTestUserUtilities::createAnonymousUser(true, true);
        $rest = SugarTestRestUtilities::getRestServiceMock($admin);
        $result = $this->api->updateCustomTheme(
            $rest,
            ['Border' => '#AAAAAA']
        );
        $this->assertNotEmpty($result);
    }
}
