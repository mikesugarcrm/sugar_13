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

class TemplateAutoIncrementTest extends TestCase
{
    /**
     * @var mixed
     */
    public $field;
    public $custom_module;
    private $moduleName = 'Accounts';

    protected function setUp(): void
    {
        $this->field = get_widget('autoincrement');
        $this->field->id = $this->moduleName . 'test_num_c';
        $this->field->name = 'test_num_c';
        $this->field->vname = 'LBL_Test';
        $this->field->comments = null;
        $this->field->help = null;
        $this->custom_module = $this->moduleName;
        $this->field->type = 'autoincrement';
        $this->field->len = 11;
        $this->field->required = 0;
        $this->field->default_value = null;
        $this->field->date_modified = '2009-09-14 02:23:23';
        $this->field->deleted = 0;
        $this->field->audited = 0;
        $this->field->massupdate = 0;
        $this->field->duplicate_merge = 0;
        $this->field->reportable = 1;
        $this->field->importable = 'false';
        $this->field->ext1 = null;
        $this->field->ext2 = null;
        $this->field->ext3 = null;
        $this->field->ext4 = null;
        $this->field->auto_increment = true;
        $this->field->autoinc_next = 1;
    }

    public function testPopulateAutoincNextFromExistingAutoincValue()
    {
        $dbStub = $this->createPartialMock('MysqliManager', ['getAutoIncrement']);
        $dbStub->method('getAutoIncrement')->willReturn(5);

        SugarTestHelper::setUp('mock_db', $dbStub);

        $dbObjectStub = $this->createPartialMock('TemplateAutoIncrement', ['getDbObject']);
        $dbObjectStub->method('getDbObject')->willReturn($dbStub);

        $this->field->autoinc_next = 1;
        $this->field->tablename = 'accounts_cstm';

        $vardef = $this->field->get_field_def();
        $this->assertEquals(5, $vardef['autoinc_next'], 'The autoinc_next value was not updated correctly');
    }
}
