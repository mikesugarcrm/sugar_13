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

class Bug46152_P4Test extends TestCase
{
    /**
     * @var mixed|\DynamicField
     */
    public $dynamicField;
    private $module = 'Notes';
    private $object = 'Note';
    private $relatedModule = 'Opportunities';

    /**
     * Tested removing a field in studio.
     *
     * @group 46152
     */
    public function testDelete()
    {
        $fieldName = 'test' . time();
        $field = $this->addField($fieldName);
        SugarTestHelper::setUp('mod_strings', [$this->module]);


        $idName = $GLOBALS['dictionary'][$this->object]['fields'][$field->name]['id_name'];
        $vName = $GLOBALS['dictionary'][$this->object]['fields'][$idName]['vname'];

        $field->delete($this->dynamicField);

        SugarTestHelper::setUp('mod_strings', [$this->module]);

        $this->assertArrayNotHasKey($field->name, $GLOBALS['dictionary'][$this->object]['fields']);
        $this->assertArrayNotHasKey($idName, $GLOBALS['dictionary'][$this->object]['fields']);
        $this->assertArrayNotHasKey($vName, $GLOBALS['mod_strings']);
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

        $field->ext2 = $this->relatedModule;
        $field->label_value = $name;
        $field->save($this->dynamicField);

        return $field;
    }

    protected function setUp(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', [$this->module]);
        SugarTestHelper::setUp('mod_strings', ['ModuleBuilder']);

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('current_user');

        $this->dynamicField = new DynamicField($this->module);
        $this->dynamicField->setup(BeanFactory::newBean($this->module));
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }
}
