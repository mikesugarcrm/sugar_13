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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers FormulaHelper
 */
class FormulaHelperTest extends TestCase
{
    private $userFieldDefs = [
        // Allowed: type allowed, flagged as true
        'my_name' => [
            'name' => 'my_name',
            'type' => 'name',
            'calculation_visible' => true,
        ],
        // Disallowed: type disallowed, not flagged (defaults to false)
        'my_iframe' => [
            'name' => 'my_iframe',
            'type' => 'iframe',
        ],
        // Disallowed: type disallowed, flagged as true (but doesn't matter)
        'my_image' => [
            'name' => 'my_image',
            'type' => 'image',
            'calculation_visible' => true,
        ],
        // Disallowed: type allowed, not flagged (defaults to false)
        'my_varchar' => [
            'name' => 'my_varchar',
            'type' => 'varchar',
        ],
        // Disallowed: type allowed, flagged as false
        'my_url' => [
            'name' => 'my_url',
            'type' => 'url',
            'calculation_visible' => false,
        ],
    ];

    /**
     * @covers ::getSugarLogicFieldList
     */
    public function testGetSugarLogicFieldList()
    {

        $result = FormulaHelper::getValidUserFields($this->userFieldDefs);

        $this->assertEquals(1, sizeof($result));
        $this->assertEquals([
            'name' => 'my_name',
            'type' => 'name',
        ], $result[0]);
    }
}
