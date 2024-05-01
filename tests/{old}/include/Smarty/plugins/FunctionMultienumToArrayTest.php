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

require_once 'include/SugarSmarty/plugins/function.multienum_to_array.php';

class FunctionMultienumToArrayTest extends TestCase
{
    /**
     * @var \Sugar_Smarty|mixed
     */
    //@codingStandardsIgnoreStart
    public $_smarty;

    //@codingStandardsIgnoreEnd

    protected function setUp(): void
    {
        $this->_smarty = new Sugar_Smarty();
    }

    public function providerPassedString()
    {
        return [
            ['Employee^,^Boss', 'Cold Call', ['Employee', 'Boss']],
            ['^Employee^,^Boss^', 'Cold Call', ['Employee', 'Boss']],
            ['^Employee^', 'Cold Call', ['Employee']],
            ['Employee', 'Cold Call', ['Employee']],
            ['', '^Cold Call^', ['Cold Call']],
            [['Employee'], 'Cold Call', ['Employee']],
            [null, ['Employee'], ['Employee']],
        ];
    }

    /**
     * @ticket 21574
     * @dataProvider providerPassedString
     */
    public function testPassedString(
        $string,
        $default,
        $result
    ) {

        $params = [];
        $params['string'] = $string;
        $params['default'] = $default;

        $this->assertEquals($result, smarty_function_multienum_to_array($params, $this->_smarty));
    }

    public function testAssignSmartyVariable()
    {
        $params = [];
        $params['string'] = '^Employee^';
        $params['default'] = 'Cold Call';
        $params['assign'] = 'multi';
        smarty_function_multienum_to_array($params, $this->_smarty);

        $this->assertEquals(
            $this->_smarty->get_template_vars($params['assign']),
            ['Employee']
        );
    }
}
