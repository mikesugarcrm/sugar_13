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

class SugarChartFactoryTest extends TestCase
{
    public $engine;

    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        global $sugar_config;
        if (!empty($sugar_config['chartEngine'])) {
            $this->engine = $sugar_config['chartEngine'];
        }
    }

    protected function tearDown(): void
    {
        if (!empty($this->engine)) {
            global $sugar_config;
            $sugar_config['chartEngine'] = $this->engine;
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function testChartFactoryDefault()
    {
        $sugarChart = SugarChartFactory::getInstance();
        $name = get_class($sugarChart);
        $this->assertEquals('chartjs', $name, 'Assert chart engine defaults to chartjs');
    }

    public function testChartFactorySucrose()
    {
        $sugarChart = SugarChartFactory::getInstance('sucrose');
        $name = get_class($sugarChart);
        $this->assertEquals('sucrose', $name, 'Assert engine is sucrose');

        $sugarChart = SugarChartFactory::getInstance('sucrose', 'Reports');
        $name = get_class($sugarChart);
        $this->assertEquals('sucroseReports', $name, 'Assert chart engine is sucroseReports');
    }
}
