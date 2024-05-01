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

require_once 'include/workflow/workflow_utils.php';

class Bug37487Test extends TestCase
{
    public function testDropDownFromFunction()
    {
        $fakedBean = new stdClass();
        $fakedBean->field_defs = [
            'test' => [
                'function' => 'testList37487',
            ],
        ];

        function testList37487()
        {
            return [
                '1' => 'Value 1',
            ];
        }

        $result = translate_option_name_from_bean($fakedBean, 'test', '1');
        $this->assertEquals('Value 1', $result, 'Result should be title instead of value');
    }
}
