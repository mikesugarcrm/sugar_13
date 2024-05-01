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

require_once 'modules/UpgradeWizard/uw_utils.php';

/**
 * Bug #57162
 * Upgrader needs to handle 3-dots releases and double digit values
 *
 * @author mgusev@sugarcrm.com
 * @ticked 57162
 */
class Bug57162Test extends TestCase
{
    public function dataProvider()
    {
        return [
            ['656', ['6.5.6']],
            ['660', ['6.6.0beta1']],
            ['640', ['6.4.0rc2']],
            ['600', ['6', 3]],
            ['6601', ['6.6.0.1']],
            ['6601', ['6.6.0.1', 0]],
            ['660', ['6.6.0.1', 3]],
            ['660', ['6.6.0.1', 3, '']],
            ['66x', ['6.6.0.1', 3, 'x']],
            ['660x', ['6.6.0.1', 0, 'x']],
            ['6.6.x', ['6.6.0.1', 3, 'x', '.']],
            ['6-6-0-beta2', ['6.6.0.1', 0, 'beta2', '-']],
            ['6601', ['6.6.0.1', 0, '', '']],
            ['', ['test342lk']],
            ['650', ['6.5.6', 0, '0']],
            ['60', ['6.5.6', 2, 0]],
        ];
    }

    /**
     * Test asserts result of implodeVersion function
     *
     * @group 57162
     * @dataProvider dataProvider
     * @param string $expect version
     * @param array $params for implodeVersion function
     */
    public function testImplodeVersion($expected, $params)
    {
        $actual = call_user_func_array('implodeVersion', $params);
        $this->assertEquals($expected, $actual, 'Result is incorrect');
    }
}
