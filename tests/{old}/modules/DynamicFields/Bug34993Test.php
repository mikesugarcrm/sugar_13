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

class Bug34993Test extends TestCase
{
    /**
     * @var \Account&\PHPUnit\Framework\MockObject\MockObject|mixed
     */
    public $accountMockBean;
    private $tablename;
    private $old_installing;

    protected function setUp(): void
    {
        $this->accountMockBean = $this->getMockBuilder('Account')
            ->setMethods(['hasCustomFields'])
            ->getMock();
        $this->tablename = 'test' . date('YmdHis');
        if (isset($GLOBALS['installing'])) {
            $this->old_installing = $GLOBALS['installing'];
        }
        $GLOBALS['installing'] = true;

        $GLOBALS['db']->createTableParams(
            $this->tablename . '_cstm',
            [
                'id_c' => [
                    'name' => 'id_c',
                    'type' => 'id',
                ],
            ],
            []
        );
        $GLOBALS['db']->query("INSERT INTO {$this->tablename}_cstm (id_c) VALUES ('12345')");

        //Safety check in case the previous run had failed
        $this->clearFieldsMetaData();
    }

    protected function clearFieldsMetaData()
    {
        global $db;
        $fieldsMetaData = BeanFactory::newBean('EditCustomFields');
        $builder = $db->getConnection()->createQueryBuilder();
        $builder->delete($fieldsMetaData->getTableName())
            ->where('custom_module = ?')
            ->andWhere('name IN (?)')
            ->setParameter(0, 'Accounts')
            ->setParameter(
                1,
                ['bug34993_test_c', 'bug34993_test2_c', 'float_test1_c', 'float_test2_c'],
                \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
            )
            ->execute();
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->dropTableName($this->tablename . '_cstm');
        $this->clearFieldsMetaData();
        if (isset($this->old_installing)) {
            $GLOBALS['installing'] = $this->old_installing;
        } else {
            unset($GLOBALS['installing']);
        }

        if (file_exists('custom/Extension/modules/Accounts/Ext/Vardefs/sugarfield_bug34993_test_c.php')) {
            unlink('custom/Extension/modules/Accounts/Ext/Vardefs/sugarfield_bug34993_test_c.php');
        }

        if (file_exists('custom/Extension/modules/Accounts/Ext/Vardefs/sugarfield_bug34993_test2_c.php')) {
            unlink('custom/Extension/modules/Accounts/Ext/Vardefs/sugarfield_bug34993_test2_c.php');
        }

        if (file_exists('custom/Extension/modules/Accounts/Ext/Vardefs/sugarfield_float_test1_c.php')) {
            unlink('custom/Extension/modules/Accounts/Ext/Vardefs/sugarfield_float_test1_c.php');
        }

        if (file_exists('custom/Extension/modules/Accounts/Ext/Vardefs/sugarfield_float_test2_c.php')) {
            unlink('custom/Extension/modules/Accounts/Ext/Vardefs/sugarfield_float_test2_c.php');
        }

        VardefManager::clearVardef('Accounts', 'Account');
        VardefManager::refreshVardefs('Accounts', 'Account');
    }

    public function testCustomFieldDefaultValue()
    {
        require_once 'modules/DynamicFields/FieldCases.php';

        //Simulate create a custom text field with a default value set to 123
        $templateText = get_widget('varchar');
        $templateText->type = 'varchar';
        $templateText->view = 'edit';
        $templateText->label = 'CUSTOM TEST';
        $templateText->name = 'bug34993_test';
        $templateText->size = 20;
        $templateText->len = 255;
        $templateText->required = false;
        $templateText->default = '123';
        $templateText->default_value = '123';
        $templateText->comment = '';
        $templateText->audited = 0;
        $templateText->massupdate = 0;
        $templateText->importable = true;
        $templateText->duplicate_merge = 0;
        $templateText->reportable = 1;
        $templateText->ext1 = null;
        $templateText->ext2 = null;
        $templateText->ext3 = null;
        $templateText->ext4 = null;

        $bean = $this->accountMockBean;
        $bean->custom_fields = new DynamicField($bean->module_dir);
        $bean->custom_fields->setup($bean);

        $bean->expects($this->any())
            ->method('hasCustomFields')
            ->will($this->returnValue(true));
        $bean->table_name = $this->tablename;
        $bean->id = '12345';
        $bean->custom_fields->addFieldObject($templateText);
        $bean->custom_fields->retrieve();
        $this->assertEquals($bean->bug34993_test_c, null, 'Assert that the custom text field has a default value set to NULL');
        $bean->custom_fields->deleteField($templateText);

        //Simulate create a custom text field with a default value set to 123
        $templateText = get_widget('enum');
        $templateText->type = 'enum';
        $templateText->view = 'edit';
        $templateText->label = 'CUSTOM TEST2';
        $templateText->name = 'bug34993_test2';
        $templateText->size = 20;
        $templateText->len = 255;
        $templateText->required = false;
        $templateText->default = '123';
        $templateText->default_value = '123';
        $templateText->comment = '';
        $templateText->audited = 0;
        $templateText->massupdate = 0;
        $templateText->importable = true;
        $templateText->duplicate_merge = 0;
        $templateText->reportable = 1;
        $templateText->ext1 = 'account_type_dom';
        $templateText->ext2 = null;
        $templateText->ext3 = null;
        $templateText->ext4 = null;

        $bean = $this->accountMockBean;
        $bean->custom_fields = new DynamicField($bean->module_dir);
        $bean->custom_fields->setup($bean);

        $bean->expects($this->any())
            ->method('hasCustomFields')
            ->will($this->returnValue(true));
        $bean->table_name = $this->tablename;
        $bean->id = '12345';
        $bean->custom_fields->addFieldObject($templateText);
        $bean->custom_fields->retrieve();
        $this->assertEquals($bean->bug34993_test2_c, null, 'Assert that the custom enum field has a default value set to NULL');
        $bean->custom_fields->deleteField($templateText);
    }

    /**
     * test custom field with float type
     */
    public function testCustomFieldFloatType()
    {
        require_once 'modules/DynamicFields/FieldCases.php';

        // custom field: float type required is false
        $templateFloat = get_widget('float');
        $templateFloat->type = 'float';
        $templateFloat->view = 'edit';
        $templateFloat->vname = 'LBL_TESTFLOATFIELD';
        $templateFloat->label = 'LBL_TESTFLOATFIELD';
        $templateFloat->name = 'float_test1';
        $templateFloat->size = 20;
        $templateFloat->len = 18;

        $templateFloat->required = false;
        $templateFloat->default = '';
        $templateFloat->default_value = '';
        $templateFloat->comment = '';
        $templateFloat->audited = 0;
        $templateFloat->massupdate = 0;
        $templateFloat->importable = true;
        $templateFloat->duplicate_merge = 1;
        $templateFloat->reportable = 1;
        $templateFloat->ext1 = '8';
        $templateFloat->ext2 = null;
        $templateFloat->ext3 = null;
        $templateFloat->ext4 = null;

        $bean = $this->accountMockBean;
        $bean->custom_fields = new DynamicField($bean->module_dir);
        $bean->custom_fields->setup($bean);

        $bean->expects($this->any())
            ->method('hasCustomFields')
            ->will($this->returnValue(true));
        $bean->table_name = $this->tablename;
        $bean->id = '12345';
        $bean->custom_fields->addFieldObject($templateFloat);
        $bean->custom_fields->retrieve();
        $this->assertEquals($bean->float_test1_c, 0, 'Assert that the custom float type field with default = 0');
        $bean->custom_fields->deleteField($templateFloat);

        // custom field: float type required is false
        $templateFloat = get_widget('float');
        $templateFloat->type = 'float';
        $templateFloat->view = 'edit';
        $templateFloat->vname = 'LBL_TESTFLOATFIELD1';
        $templateFloat->label = 'LBL_TESTFLOATFIELD1';
        $templateFloat->name = 'float_test2';
        $templateFloat->size = 20;
        $templateFloat->len = 18;

        $templateFloat->required = true;
        $templateFloat->default = '';
        $templateFloat->default_value = '';
        $templateFloat->comment = '';
        $templateFloat->audited = 0;
        $templateFloat->massupdate = 0;
        $templateFloat->importable = true;
        $templateFloat->duplicate_merge = 1;
        $templateFloat->reportable = 1;
        $templateFloat->ext1 = '8';
        $templateFloat->ext2 = null;
        $templateFloat->ext3 = null;
        $templateFloat->ext4 = null;


        $bean = $this->accountMockBean;
        $bean->custom_fields = new DynamicField($bean->module_dir);
        $bean->custom_fields->setup($bean);

        $bean->expects($this->any())
            ->method('hasCustomFields')
            ->will($this->returnValue(true));
        $bean->table_name = $this->tablename;
        $bean->id = '12345';
        $bean->custom_fields->addFieldObject($templateFloat);
        $bean->custom_fields->retrieve();
        $this->assertEquals($bean->float_test2_c, 0, 'Assert that the custom float type with default value = 0');
        $bean->custom_fields->deleteField($templateFloat);
    }
}
