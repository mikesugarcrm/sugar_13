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

require_once 'include/utils.php';


class UtilsConfigTest extends TestCase
{
    /**
     * Provider for testBuiltGetSugarConfig
     *
     * Can be added to as we want to test and ensure certain configs are what we expect at build time
     */
    public function buildConfigProvider()
    {
        return [
            ['freeze_list_headers', true],
            ['allow_freeze_first_column', true],
            ['chartEngine', 'chartjs'],
            ['clear_resolved_date', true],
            ['catalog_enabled', false],
            ['catalog_url', 'https://appcatalog.service.sugarcrm.com'],
        ];
    }

    /**
     * Ensures that the configs we are set are verified to be what we expect at build time
     *
     * @dataProvider buildConfigProvider
     * @param mixed $config config to check
     */
    public function testBuiltGetSugarConfig($config, $expectedValue)
    {
        include 'config.php';
        global $sugar_config;
        $this->assertTrue(isset($sugar_config[$config]));
        $actualValue = $sugar_config[$config];
        $this->assertEquals($actualValue, $expectedValue);
    }
}
