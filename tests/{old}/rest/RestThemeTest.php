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

use Sugarcrm\Sugarcrm\Security\ValueObjects\PlatformName;

class RestThemeTest extends RestTestBase
{
    private $platformName;

    protected function setUp(): void
    {
        $this->platformName = PlatformName::fromString('platform_TEST_123456789E_1234');
    }

    protected function tearDown(): void
    {
        // Clear out the test folders
        $customDir = 'custom/themes/clients/' . $this->platformName->value();
        if (is_dir($customDir)) {
            rmdir_recursive($customDir);
        }
        $cacheDir = 'cache/themes/clients/' . $this->platformName->value();
        if (is_dir($cacheDir)) {
            rmdir_recursive($cacheDir);
        }
        parent::tearDown();
    }

    /**
     * @group rest
     * @group Theming
     */
    public function testPreviewCSS()
    {
        $args1 = [
            'platform' => $this->platformName->value(),
            'themeName' => 'default',
            'BorderColor' => '#75c1d1',
            'NavigationBar' => '#192c47',
            'PrimaryButton' => '#f5b30a',
        ];

        $args2 = [
            'platform' => $this->platformName->value(),
            'themeName' => 'default',
            'BorderColor' => '#aaaaaa',
            'NavigationBar' => '#aaaaaa',
            'PrimaryButton' => '#aaaaaa',
        ];

        // TEST= GET bootstrap.css with a set of arguments
        $restReply1 = $this->restCall('css/preview' . $this->rawurlencode($args1));

        // TEST if the the response is not empty
        $this->assertNotEmpty($restReply1['replyRaw']);

        // TEST= GET bootstrap.css with another set of arguments
        $restReply2 = $this->restCall('css/preview' . $this->rawurlencode($args2));

        // TEST the two generated css are different
        $this->assertIsString($restReply1['replyRaw']);
        $this->assertNotEquals($restReply1['replyRaw'], $restReply2['replyRaw']);
    }

    /**
     * @group rest
     * @group Theming
     */
    public function testGetCustomThemeVars()
    {
        // TEST= GET theme
        $restReply = $this->restCall('theme?platform=' . $this->platformName->value());

        // TEST we get a hash of variables
        $this->assertEquals(['name' => 'BorderColor', 'value' => '#E61718'], $restReply['reply']['hex'][0]);
        $this->assertEquals(['name' => 'NavigationBar', 'value' => '#000000'], $restReply['reply']['hex'][1]);
        $this->assertEquals(['name' => 'PrimaryButton', 'value' => '#177EE5'], $restReply['reply']['hex'][2]);
    }

    /**
     * @group rest
     * @group Theming
     */
    public function testUpdateCustomTheme()
    {
        $args = [
            'platform' => $this->platformName->value(),
            'themeName' => 'default',
            'BorderColor' => '#75c1d1',
            'NavigationBar' => '#192c47',
            'PrimaryButton' => '#f5b30a',
        ];

        // Fake the user is an admin
        $this->user->is_admin = 1;
        $this->user->save();
        $GLOBALS['db']->commit();
        // TEST= POST theme
        $restReply = $this->restCall('theme', json_encode($args));

        $this->user->is_admin = 0;
        $this->user->save();
        $GLOBALS['db']->commit();

        // TEST the css files have been created
        $this->assertArrayHasKey('bootstrap', $restReply['reply']);
        $this->assertArrayHasKey('sugar', $restReply['reply']);
        $explode = explode('/', $restReply['reply']['bootstrap']);
        $bootstrapFileName = end($explode);
        $explode2 = explode('/', $restReply['reply']['sugar']);
        $sugarFileName = end($explode2);
        $bootstrapFile = sugar_cached(
            'themes/clients/' . $args['platform'] . '/' . $args['themeName'] . '/' . $bootstrapFileName
        );
        $sugarFile = sugar_cached(
            'themes/clients/' . $args['platform'] . '/' . $args['themeName'] . '/' . $sugarFileName
        );
        $this->assertFileExists($bootstrapFile, 'Created file (' . $bootstrapFileName . ') does not exist');
        $this->assertFileExists($sugarFile, 'Created file (' . $sugarFileName . ') does not exist');

        // TEST the css files are not empty
        $this->assertTrue(filesize($bootstrapFile) > 0, 'Created file (' . $bootstrapFileName . ') has no contents');
        $this->assertTrue(filesize($sugarFile) > 0, 'Created file (' . $sugarFileName . ') has no contents');

        $thisTheme = new SidecarTheme($args['platform']);

        // TEST we have updated the variables in variables.less
        $variables = $thisTheme->loadVariables();
        $this->assertEquals($args['BorderColor'], $variables['BorderColor']);
        $this->assertEquals($args['NavigationBar'], $variables['NavigationBar']);
        $this->assertEquals($args['PrimaryButton'], $variables['PrimaryButton']);

        // TEST if a config var has been added in the DB
        $query = $GLOBALS['db']->query(
            "SELECT value FROM config WHERE category = '" . $args['platform'] . "' AND name = 'css'"
        );
        $row = $GLOBALS['db']->fetchByAssoc($query);

        // TEST the config var contains the bootstrap.css url
        $this->assertEquals(
            // Some databases (*cough* ORACLE *cough*) are backslash escaping this value
            stripslashes(html_entity_decode($row['value'], ENT_COMPAT)),
            stripslashes($restReply['replyRaw']),
            "$row[value] does not match the expected value"
        );
    }

    /**
     * @group rest
     * @group Theming
     */
    public function testResetDefaultTheme()
    {
        $args = [
            'platform' => $this->platformName->value(),
            'themeName' => 'default',
            'BorderColor' => '#75c1d1',
            'NavigationBar' => '#192c47',
            'PrimaryButton' => '#f5b30a',
        ];

        // Fake the user is an admin
        $this->user->is_admin = 1;
        $this->user->save();
        $GLOBALS['db']->commit();
        // TEST= POST theme
        $this->restCall('theme', json_encode($args));
        $thisTheme = new SidecarTheme($args['platform']);
        // TEST we have updated the variables in variables.less
        $variables = $thisTheme->loadVariables();
        $this->assertEquals($args['BorderColor'], $variables['BorderColor']);
        $this->assertEquals($args['NavigationBar'], $variables['NavigationBar']);
        $this->assertEquals($args['PrimaryButton'], $variables['PrimaryButton']);

        $args = [
            'platform' => $this->platformName->value(),
            'themeName' => 'default',
            'BorderColor' => '#ABCDEF',
            'NavigationBar' => '#ABCDEF',
            'PrimaryButton' => '#ABCDEF',
            'reset' => 'true',
        ];

        // TEST= POST theme with reset=true
        $this->restCall('theme', json_encode($args));

        $this->user->is_admin = 0;
        $this->user->save();
        $GLOBALS['db']->commit();

        // TEST variables.less generated in the custom folder is the same as the default theme
        $thisTheme = new SidecarTheme($args['platform']);
        $this->assertNotEquals($variables, $thisTheme->loadVariables());
    }

    /**
     * @group rest
     * @group Theming
     */
    //Bug58031: baseUrl needs to be different for the Theme Editor preview.
    public function testBug58031BaseUrlVariable()
    {
        // TEST 1:  for preview, baseUrl is "../../styleguide/assets"
        $args = [
            'platform' => $this->platformName->value(),
            'themeName' => 'default',
            'BorderColor' => '#75c1d1',
            'NavigationBar' => '#192c47',
            'PrimaryButton' => '#f5b30a',
        ];
        $restReply = $this->restCall('css/preview' . $this->rawurlencode($args));

        // TEST= the CSS contains the expected baseUrl
        $this->assertContains('../../styleguide/assets', $restReply['replyRaw']);
        $this->assertNotContains('../../../../../styleguide/assets', $restReply['replyRaw']);

        // TEST 2:  for deployment, baseUrl is "../../../../../styleguide/assets"
        $theme = new SidecarTheme($this->platformName->value());
        $css = $theme->previewCss();
        // TEST= the CSS contains the expected baseUrl
        $this->assertContains('../../../../../styleguide/assets', $css);
    }

    private function rawurlencode($args)
    {
        $getString = '?';
        foreach ($args as $k => $v) {
            $getString .= $k . '=' . rawurlencode($v) . '&';
        }
        return $getString;
    }
}
