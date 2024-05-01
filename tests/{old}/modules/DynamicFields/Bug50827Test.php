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

class Bug50827Test extends TestCase
{
    private $smarty;

    protected function setUp(): void
    {
        $this->smarty = new Sugar_Smarty();
    }

    protected function tearDown(): void
    {
        unset($this->smarty);
    }

    /**
     * @dataProvider bug50827DataProvider
     */
    public function testCalculatedVisible($vardef, $expected)
    {
        $this->smarty->assign('vardef', $vardef);
        $this->smarty->assign('mod_strings', $GLOBALS['mod_strings']);
        $output = $this->smarty->fetch('modules/DynamicFields/templates/Fields/Forms/coreDependent.tpl');

        if ($expected) {
            $this->assertStringContainsString('name="calculated" id="calculated"', $output);
        } else {
            $this->assertStringNotContainsString('name="calculated" id="calculated"', $output);
        }
    }

    /**
     * Data provider for testCalculatedVisible()
     * @return array vardef, expected
     */
    public function bug50827DataProvider()
    {
        return [
            0 => [
                [
                    'name' => 'email1',
                    'vname' => 'LBL_EMAIL_ADDRESS',
                    'type' => 'varchar',
                    'function' => [
                        'name' => 'getEmailAddressWidget',
                        'returns' => 'html',
                    ],
                    'source' => 'non-db',
                    'group' => 'email1',
                    'merge_filter' => 'enabled',
                    'studio' => ['editField' => true, 'searchview' => false, 'popupsearch' => false],
                ],
                false,
            ],
            1 => [
                [
                    'name' => 'email1',
                    'vname' => 'LBL_EMAIL_ADDRESS',
                    'type' => 'varchar',
                    'source' => 'non-db',
                    'group' => 'email1',
                    'merge_filter' => 'enabled',
                    'studio' => ['editField' => true, 'searchview' => false, 'popupsearch' => false, 'calculated' => true],
                ],
                true,
            ],
            2 => [
                [
                    'name' => 'email1',
                    'vname' => 'LBL_EMAIL_ADDRESS',
                    'type' => 'varchar',
                    'source' => 'non-db',
                    'group' => 'email1',
                    'merge_filter' => 'enabled',
                    'studio' => ['editField' => true, 'searchview' => false, 'popupsearch' => false, 'calculated' => false],
                ],
                false,
            ],
            3 => [
                [
                    'name' => 'email1',
                    'vname' => 'LBL_EMAIL_ADDRESS',
                    'type' => 'varchar',
                    'source' => 'non-db',
                    'group' => 'email1',
                    'merge_filter' => 'enabled',
                    'studio' => ['editField' => true, 'searchview' => false, 'popupsearch' => false],
                ],
                true,
            ],
        ];
    }
}
