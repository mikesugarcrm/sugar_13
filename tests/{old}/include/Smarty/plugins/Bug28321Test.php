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

class Bug28321Test extends TestCase
{
    public function testCustomCodeRenderField()
    {
        $config = null;
        $params = [
            'var' => [
                'name' => 'name',
                'comment' => 'Name of the Company',
                'label' => 'LBL_NAME',
                'displayParams' => [
                    'enableConnectors' => '1',
                    'module' => 'Accounts',
                    'connectors' => [
                        '0' => 'ext_rest_twitter',
                    ],
                ],

                'customCodeRenderField' => '1',
                'customCode' => '<a href="http://www.google.com?q={$fields.name.value}">Search</a>',
                'tabindex' => '100',
            ],

            'colData' => [
                'field' => [
                    'name' => 'name',
                    'comment' => 'Name of the Company',
                    'label' => 'LBL_NAME',
                    'displayParams' => [
                        'enableConnectors' => '1',
                        'module' => 'Accounts',
                        'connectors' => [
                            '0' => 'ext_rest_twitter',
                        ],
                    ],

                    'customCodeRenderField' => '1',
                    'customCode' => '<a href="http://www.google.com?q={$fields.name.value}">Search</a>',
                    'tabindex' => '100',
                ],
            ],
        ];

        $ss = new Sugar_Smarty();
        include 'modules/Connectors/connectors/sources/ext/rest/twitter/config.php';
        $ss->assign('config', $config);
        $ss->left_delimiter = '{{';
        $ss->right_delimiter = '}}';
        require_once 'include/SugarSmarty/plugins/function.sugar_evalcolumn.php';
        $output = smarty_function_sugar_evalcolumn($params, $ss);

        //Doing this the hack way...customCode is 65 chars long.
        //if customCodeRenderField is set then the connectors have already been processed
        $this->assertEquals(65, strlen($output), 'Connectors should not be processed when customCode is set to customCodeRenderField');

        //now if customCodeRenderField is not set then process as normal
        unset($params['var']['customCodeRenderField']);
        unset($params['colData']['field']['customCodeRenderField']);
        if (!isset($GLOBALS['app_strings'])) {
            $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        }
        $output = smarty_function_sugar_evalcolumn($params, $ss);

        $this->assertGreaterThan(65, strlen($output), 'Connectors should not be processed when customCode is set to customCodeRenderField');
    }
}
