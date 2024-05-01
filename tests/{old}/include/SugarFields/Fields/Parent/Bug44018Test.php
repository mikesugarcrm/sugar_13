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

class Bug44018Test extends TestCase
{
    public function testGetSearchInput()
    {
        $sugarField = new SugarFieldParent('Parent');

        $args = ['searchFormTab' => 'basic_search', 'parent_type_basic' => 'Accounts'];
        $result = $sugarField->getSearchInput('parent_type', $args);
        $this->assertEquals($result, 'Accounts', 'Assert that basic search for parent type works');

        $args = ['searchFormTab' => 'advanced_search', 'parent_type_advanced' => 'Contacts'];
        $result = $sugarField->getSearchInput('parent_type', $args);
        $this->assertEquals($result, 'Contacts', 'Assert that advanced search for parent type works');

        $args = ['parent_type' => 'Contacts'];
        $result = $sugarField->getSearchInput('parent_type', $args);
        $this->assertEquals($result, 'Contacts', 'Assert that search for parent_type workds');
    }
}
