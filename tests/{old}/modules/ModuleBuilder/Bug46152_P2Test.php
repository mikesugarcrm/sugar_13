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

require_once 'modules/DynamicFields/FieldCases.php';

class Bug46152_P2Test extends TestCase
{
    private $fields = [];
    private $dynamicField = null;

    /**
     * Test is id fields have unique label
     *
     * Create 2 equal fields. Test is id fields have unique label. For correct import we must have unique label of id fields.
     *
     * @group 46152
     */
    public function testDoubleLabel()
    {
        $idName1 = $GLOBALS['dictionary']['Note']['fields'][$this->fields[0]->name]['id_name'];
        $idName2 = $GLOBALS['dictionary']['Note']['fields'][$this->fields[1]->name]['id_name'];
        $vName1 = $GLOBALS['dictionary']['Note']['fields'][$idName1]['vname'];
        $vName2 = $GLOBALS['dictionary']['Note']['fields'][$idName2]['vname'];

        $this->assertArrayHasKey($vName1, $GLOBALS['mod_strings']);
        $this->assertArrayHasKey($vName2, $GLOBALS['mod_strings']);

        $this->assertNotEquals($GLOBALS['mod_strings'][$vName1], $GLOBALS['mod_strings'][$vName2]);
    }

    protected function setUp(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', ['Notes']);
        SugarTestHelper::setUp('mod_strings', ['ModuleBuilder']);

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('current_user');

        $this->dynamicField = new DynamicField('Notes');
        $this->dynamicField->setup(BeanFactory::newBean('Notes'));

        $this->addField('testfield1_b46152');
        $this->addField('testfield2_b46152');

        SugarTestHelper::setUp('mod_strings', ['Notes']);
    }

    private function addField($name)
    {
        $labelName = 'LBL_' . strtoupper($name);
        $field = get_widget('relate');
        $field->audited = 0;
        $field->view = 'edit';
        $field->name = $name;
        $field->vname = $labelName;
        $field->label = $labelName;

        $field->ext2 = 'Opportunities';
        $field->label_value = $name;
        $field->save($this->dynamicField);
        $this->fields[] = $field;
    }

    protected function tearDown(): void
    {
        $this->deleteFields();

        SugarTestHelper::tearDown();
    }

    private function deleteFields()
    {
        foreach ($this->fields as $field) {
            $field->delete($this->dynamicField);
        }
    }
}
