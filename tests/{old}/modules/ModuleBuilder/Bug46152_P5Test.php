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

class Bug46152_P5Test extends TestCase
{
    private static $moduleBuilder;

    private static $packageName = 'pnb46152';
    private static $moduleName = 'modb46152';

    private static $fieldName = 'fldb46152';

    private $field;
    private $relatedModule = 'Opportunities';
    private $fieldLabelName = 'Opportunities';

    /**
     * Testing creation a field in Module Builder. Also test MBModule::getField
     *
     * @group 46152
     */
    public function testMBAddField()
    {
        $this->createField();

        $module = self::$moduleBuilder->getPackage(self::$packageName)->getModule(self::$moduleName);

        $modStrings = $module->getModStrings($GLOBALS['current_language']);

        $field = $module->getField(self::$fieldName);

        $this->assertNotNull($field);

        $fieldId = $module->getField($field->id_name);

        $this->assertNotNull($fieldId);

        $this->assertNotEmpty($field->vname);
        $this->assertNotEmpty($fieldId->vname);

        $this->assertArrayHasKey($field->vname, $modStrings);
        $this->assertArrayHasKey($fieldId->vname, $modStrings);
        $this->assertEquals($this->relatedModule, $field->ext2);

        return $field->id_name;
    }

    /**
     * Testing deleting a field in Module Builder. Also test MBModule::getField
     *
     * @group 46152
     * @depends testMBAddField
     */
    public function testMBDeleteField($idFieldName)
    {
        $module = self::$moduleBuilder->getPackage(self::$packageName)->getModule(self::$moduleName);
        $field = $module->getField(self::$fieldName);
        $fieldId = $module->getField($field->id_name);

        $field->delete($module);

        $modStrings = $module->getModStrings($GLOBALS['current_language']);

        $this->assertNull($module->getField(self::$fieldName));
        $this->assertNull($module->getField($idFieldName));
        $this->assertArrayNotHasKey($fieldId->vname, $modStrings);
    }

    private function createField()
    {
        $this->fieldLabelName = 'LBL_' . strtoupper(self::$fieldName);
        $this->field = get_widget('relate');
        $this->field->audited = 0;
        $this->field->view = 'edit';
        $this->field->name = self::$fieldName;
        $this->field->vname = $this->fieldLabelName;
        $this->field->label = $this->fieldLabelName;
        $this->field->ext2 = $this->relatedModule;
        $this->field->label_value = self::$fieldName;


        $module = self::$moduleBuilder->getPackage(self::$packageName)->getModule(self::$moduleName);
        $this->field->save($module);
        $module->mbvardefs->save();
        $module->setLabel($GLOBALS['current_language'], $this->fieldLabelName, self::$fieldName);
        $module->save();
    }

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', ['ModuleBuilder']);
        SugarTestHelper::setUp('current_user');
        self::createPackage();
        self::createModule();
    }

    private static function createPackage()
    {
        self::$moduleBuilder = new ModuleBuilder();
        $package = self::$moduleBuilder->getPackage(self::$packageName);
        $_REQUEST['key'] = self::$packageName;
        $_REQUEST['description'] = '';
        $_REQUEST['author'] = '';
        $_REQUEST['readme'] = '';

        $package->populateFromPost();
        $package->loadModules();
        self::$moduleBuilder->save();
    }

    public static function createModule()
    {
        $module = self::$moduleBuilder->getPackage(self::$packageName)->getModule(self::$moduleName);
        $_REQUEST ['team_security'] = 1;
        $_REQUEST ['has_tab'] = 1;
        $_REQUEST ['type'] = 'company';
        $_REQUEST ['label'] = self::$moduleName;
        $module->populateFromPost();
        self::$moduleBuilder->save();
    }

    public static function tearDownAfterClass(): void
    {
        self::$moduleBuilder->getPackage(self::$packageName)->delete();

        $_REQUEST = [];
        SugarTestHelper::tearDown();
    }
}
