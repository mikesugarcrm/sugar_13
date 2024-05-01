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

use Sugarcrm\Sugarcrm\DependencyInjection\Container;

require_once 'upgrade/scripts/post/3_UpdateAnalyticsSettings.php';

/**
 * @coversDefaultClass SugarUpgradeUpdateAnalyticsSettings
 */
class SugarUpgradeUpdateAnalyticsSettingsTest extends UpgradeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Back up the configuration override.
        SugarTestHelper::saveFile('config_override.php');
    }

    /**
     * @covers ::run
     */
    public function testRun()
    {
        $script = new SugarUpgradeUpdateAnalyticsSettings($this->upgrader);
        $script->upgrader->from_version = '8.0.0';
        $config = new Configurator();
        $config->config['analytics'] = [
            'enabled' => true,
            'connector' => 'GoogleAnalytics',
            'id' => 'GID',
        ];
        $config->saveConfig();
        $script->run();
        $actual = Container::getInstance()->get(SugarConfig::class)->get('analytics');
        $this->assertEquals(true, $actual['enabled']);
        $this->assertEquals('Pendo', $actual['connector']);
    }
}
