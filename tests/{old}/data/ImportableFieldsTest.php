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

class ImportableFieldsTest extends TestCase
{
    public $time_date;
    protected $myBean;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');

        $this->myBean = new SugarBean();

        $this->myBean->module_dir = 'myBean';

        $this->myBean->field_defs = [
            'id' => ['name' => 'id', 'vname' => 'LBL_ID', 'type' => 'id', 'required' => true,],
            'name' => ['name' => 'name', 'vname' => 'LBL_NAME', 'type' => 'varchar', 'len' => '255', 'importable' => 'required',],
            'bool_field' => ['name' => 'bool_field', 'vname' => 'LBL_BOOL_FIELD', 'type' => 'bool', 'importable' => false,],
            'int_field' => ['name' => 'int_field', 'vname' => 'LBL_INT_FIELD', 'type' => 'int',],
            'autoinc_field' => ['name' => 'autoinc_field', 'vname' => 'LBL_AUTOINC_FIELD', 'type' => 'true', 'auto_increment' => true,],
            'float_field' => ['name' => 'float_field', 'vname' => 'LBL_FLOAT_FIELD', 'type' => 'float', 'precision' => 2,],
            'date_field' => ['name' => 'date_field', 'vname' => 'LBL_DATE_FIELD', 'type' => 'date',],
            'time_field' => ['name' => 'time_field', 'vname' => 'LBL_TIME_FIELD', 'type' => 'time', 'importable' => 'false',],
            'image_field' => ['name' => 'image_field', 'vname' => 'LBL_IMAGE_FIELD', 'type' => 'image',],
            'datetime_field' => ['name' => 'datetime_field', 'vname' => 'LBL_DATETIME_FIELD', 'type' => 'datetime',],
            'link_field1' => ['name' => 'link_field1', 'type' => 'link',],
            'link_field2' => ['name' => 'link_field1', 'type' => 'link', 'importable' => true,],
            'link_field3' => ['name' => 'link_field1', 'type' => 'link', 'importable' => 'true',],
        ];
    }

    protected function tearDown(): void
    {
        unset($this->time_date);
    }

    /**
     * @ticket 31397
     */
    public function testImportableFields()
    {
        $fields = [
            'id',
            'name',
            'int_field',
            'float_field',
            'date_field',
            'datetime_field',
            'link_field2',
            'link_field3',
        ];
        $this->assertEquals(
            $fields,
            array_keys($this->myBean->get_importable_fields())
        );
    }

    /**
     * @ticket 31397
     */
    public function testImportableRequiredFields()
    {
        $fields = [
            'name',
        ];
        $this->assertEquals(
            $fields,
            array_keys($this->myBean->get_import_required_fields())
        );
    }

    public function testImportableFieldsACL()
    {
        $fields = [
            'id',
            'name',
            'int_field',
            'float_field',
            'datetime_field',
            'link_field2',
            'link_field3',
        ];

        $aclmyBean = new TestSugarACLStaticPAT249();
        $aclmyBean->return_value = ['date_field' => false]; // no access to this field
        SugarACL::resetACLs();
        SugarACL::$acls[$this->myBean->module_dir] = [$aclmyBean];

        $this->assertEquals(
            $fields,
            array_keys($this->myBean->get_importable_fields())
        );

        SugarACL::resetACLs();
    }
}

class TestSugarACLStaticPAT249 extends SugarACLStatic
{
    public $return_value = null;

    public function checkFieldList($module, $field_list, $action, $context)
    {
        return $this->return_value;
    }
}
