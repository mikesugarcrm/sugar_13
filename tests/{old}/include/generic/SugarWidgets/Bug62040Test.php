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
 * Bug #62040
 * Dashlet Filter for custom Users relationship on custom module empty
 *
 * @author bsitnikovski@sugarcrm.com
 * @ticket 62040
 */
class Bug62040Test extends TestCase
{
    private $contact;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->first_name = 'Boro';
        $this->contact->last_name = 'SugarTest';
        $this->contact->save();
    }

    public function testConcatName()
    {
        $layoutDef = [
            'table' => $this->contact->table_name,
            'input_name0' => [],
            'source' => 'non-db',
            'name' => 'contacts',
            'rname' => 'last_name',
            'db_concat_fields' => ['first_name', 'last_name'],
            'module' => 'Contacts',
        ];
        $html = $this->getSugarWidgetFieldRelate()->displayInput($layoutDef);
        $this->assertStringContainsString('Boro SugarTest', $html);
    }

    private function getSugarWidgetFieldRelate()
    {
        $LayoutManager = new LayoutManager();
        $temp = new Report();
        $LayoutManager->setAttributePtr('reporter', $temp);
        $Widget = new SugarWidgetFieldRelate($LayoutManager);
        return $Widget;
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }
}
