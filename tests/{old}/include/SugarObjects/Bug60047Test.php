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
 * @group bug60047
 */
class Bug60047Test extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        // Force a vardef refresh forcefully because some tests in the suite
        // actively destroy some globals. This will have an effect on getBean
        // for the duration of this test
        $GLOBALS['reload_vardefs'] = true;
        $_SESSION['developerMode'] = true;
    }

    public static function tearDownAfterClass(): void
    {
        unset($_SESSION['developerMode']);
        SugarTestHelper::tearDown();
    }

    public function testForecastBean()
    {
        VardefManager::loadVardef('Forecasts', 'Forecast', true);
        $this->assertArrayHasKey('acls', $GLOBALS['dictionary']['Forecast']);
        $this->assertArrayHasKey('SugarACLStatic', $GLOBALS['dictionary']['Forecast']['acls']);
    }

    public function get_beans()
    {
        return [
            ['ForecastOpportunities'],
        ];
    }

    /**
     * @dataProvider get_beans
     */
    public function testForecastSubordinateBean($module)
    {
        // drop forecasting vardefs
        foreach (glob('cache/modules/Forecasts/*vardefs.php') as $file) {
            @unlink($file);
        }
        $bean = BeanFactory::newBean($module);
        $this->assertNotEmpty($bean);
        $this->assertTrue(empty($GLOBALS['dictionary'][$bean->object_name]['acls']));
    }
}
