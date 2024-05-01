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
 * Bug #39749
 * Quick Create in Studio
 * @ticket 39749
 * TODO: Make this test go away when all modules are out of BWC
 * This test only applies to modules in BWC
 */
class Bug39749Test extends TestCase
{
    public function editModules()
    {
        return [
            ['module' => 'ProjectTask', 'get_quick_create' => '0'],
            ['module' => 'Campaigns', 'get_quick_create' => '0'],
            ['module' => 'ProductTemplates', 'get_quick_create' => '0'],
            ['module' => 'Accounts', 'get_quick_create' => '0'],
            ['module' => 'Quotes', 'get_quick_create' => '0'],
        ];
    }

    /**
     * @group 39749
     * @dataProvider editModules
     */
    public function testGetVariableMap($module, $get_quick_create)
    {
        $vl = new ViewLabels();

        $varMap = $vl->getVariableMap($module);

        $this->assertTrue((isset($varMap['quickcreate']) == $get_quick_create));
    }
}
