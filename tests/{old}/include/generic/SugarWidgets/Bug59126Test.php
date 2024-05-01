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

class Bug59126Test extends TestCase
{
    private $contact;

    public function testLastName()
    {
        $layoutDef = [
            'table' => $this->contact->table_name,
            'input_name0' => [],
            'name' => 'contacts',
            'rname' => 'last_name',
        ];
        $html = $this->getSugarWidgetFieldRelate()->displayInput($layoutDef);
        $regExpPattern = $this->getAssertRegExp($this->contact->id, "{$this->contact->last_name}");
        $this->assertMatchesRegularExpression($regExpPattern, $html);
    }

    public function testFirstLastName()
    {
        $layoutDef = [
            'table' => $this->contact->table_name,
            'input_name0' => [],
            'name' => 'contacts',
            'rname' => 'last_name',
            'db_concat_fields' => ['first_name', 'last_name'],
        ];
        $html = $this->getSugarWidgetFieldRelate()->displayInput($layoutDef);
        $regExpPattern = $this->getAssertRegExp(
            $this->contact->id,
            "{$this->contact->first_name}\s+{$this->contact->last_name}"
        );
        $this->assertMatchesRegularExpression($regExpPattern, $html);
    }

    public function testCustomField()
    {
        $layoutDef = [
            'table' => $this->contact->table_name,
            'module' => $this->contact->module_name,
            'custom_module' => 'Contacts',
            'input_name0' => [],
            'name' => 'customField',
            'rname' => 'name',
        ];
        $html = $this->getSugarWidgetFieldRelate()->displayInput($layoutDef);
        $regExpPattern = $this->getAssertRegExp(
            $this->contact->id,
            "{$this->contact->first_name}\s+{$this->contact->last_name}"
        );
        $this->assertMatchesRegularExpression($regExpPattern, $html);
    }

    private function getAssertRegExp($value, $text)
    {
        $pattern = '/\<option.+value="' . $value . '".*\>' . $text . '\<\/option\>/i';
        return $pattern;
    }

    private function getSugarWidgetFieldRelate()
    {
        $LayoutManager = new LayoutManager();
        $temp = (object)['db' => $GLOBALS['db'], 'report_def_str' => ''];
        $LayoutManager->setAttributePtr('reporter', $temp);
        $Widget = new SugarWidgetFieldRelate($LayoutManager);
        return $Widget;
    }

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        $this->contact = SugarTestContactUtilities::createContact();
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }
}
