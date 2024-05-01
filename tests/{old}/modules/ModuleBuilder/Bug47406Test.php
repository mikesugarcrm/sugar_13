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

if (file_exists('custom/include/SugarSmarty/plugins/function.sugar_currency_format.php')) {
    require_once 'custom/include/SugarSmarty/plugins/function.sugar_currency_format.php';
} else {
    require_once 'include/SugarSmarty/plugins/function.sugar_currency_format.php';
}

class Bug47406Test extends TestCase
{
    private $mbvardef;
    private $smarty;

    protected function setUp(): void
    {
        $this->smarty = new Smarty();
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->mbvardef);
        unset($this->smarty);
        unset($GLOBALS['current_user']);
    }

    public function providerMBVardefAddFieldVardef()
    {
        return [
            [['name' => 'field_name', 'default' => 0], 0],
            [['name' => 'field_name', 'default' => '0'], '0'],
            [['name' => 'field_name', 'default' => '0.00'], '0.00'],
            [['name' => 'field_name', 'default' => ' '], ' '],
        ];
    }

    /**
     * @dataProvider providerMBVardefAddFieldVardef
     */
    public function testMBVardefAddFieldVardef($vardef, $expected)
    {
        unset($this->mbvardef);

        $this->mbvardef = $this->getMockBuilder('MBVardefs')->disableOriginalConstructor()
            ->setMethods(['load'])
            ->setConstructorArgs(['name', 'path', 'key'])
            ->getMock();

        $this->mbvardef->addFieldVardef($vardef);

        $this->assertEquals($expected, $this->mbvardef->vardef['fields'][$vardef['name']]['default']);
    }
}
