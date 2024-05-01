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

/**
 * Bug #56423
 * @ticket 56423
 */
class Bug56423Test extends TestCase
{
    /**
     * @var TemplateRelatedTextField
     */
    protected $accountFieldWidget;
    /**
     * @var DynamicField
     */
    protected $accountField;
    /**
     * @var TemplateRelatedTextField
     */
    protected $opportunityFieldWidget;
    /**
     * @var DynamicField
     */
    protected $opportunityField;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', ['ModuleBuilder']);
        SugarTestHelper::setUp('current_user', [true, 1]);

        $_POST = $_REQUEST = $this->getPostData();

        $this->accountFieldWidget = get_widget($_REQUEST['type']);
        $this->accountFieldWidget->populateFromPost();
        $module = $_REQUEST['view_module'];
        $this->accountField = new DynamicField($module);
        $class_name = $GLOBALS['beanList'][$module];
        require_once $GLOBALS['beanFiles'][$class_name];
        $mod = new $class_name();
        $this->accountField->setup($mod);
        $this->accountFieldWidget->save($this->accountField);

        $_POST['view_module'] = $_REQUEST['view_module'] = 'Opportunities';

        $this->opportunityFieldWidget = get_widget($_REQUEST['type']);
        $this->opportunityFieldWidget->populateFromPost();
        $module = $_REQUEST['view_module'];
        $this->opportunityField = new DynamicField($module);
        $class_name = $GLOBALS['beanList'][$module];
        require_once $GLOBALS['beanFiles'][$class_name];
        $mod = new $class_name();
        $this->opportunityField->setup($mod);
        $this->opportunityFieldWidget->save($this->opportunityField);

        $repair = new RepairAndClear();
        $repair->repairAndClearAll(
            ['rebuildExtensions', 'clearVardefs'],
            [$GLOBALS['beanList']['Accounts'], $GLOBALS['beanList']['Opportunities']],
            true,
            false
        );
    }

    public function getPostData()
    {
        return [
            'module' => 'ModuleBuilder',
            'action' => 'saveField',
            'new_dropdown' => '',
            'to_pdf' => 'true',
            'view_module' => 'Accounts',
            'is_update' => 'true',
            'type' => 'relate',
            'name' => 'contact',
            'labelValue' => 'contact',
            'label' => 'LBL_CONTACT',
            'help' => '',
            'comments' => '',
            'ext2' => 'Contacts',
            'ext3' => '',
            'dependency' => '',
            'dependency_display' => '',
            'reportableCheckbox' => '1',
            'reportable' => '1',
            'importable' => 'true',
            'duplicate_merge' => '0',
        ];
    }

    protected function tearDown(): void
    {
        if ($this->accountFieldWidget) {
            $this->accountFieldWidget->delete($this->accountField);
        }
        if ($this->opportunityFieldWidget) {
            $this->opportunityFieldWidget->delete($this->opportunityField);
        }

        $repair = new RepairAndClear();
        $repair->repairAndClearAll(
            ['rebuildExtensions', 'clearVardefs'],
            [$GLOBALS['beanList']['Accounts'], $GLOBALS['beanList']['Opportunities']],
            true,
            false
        );

        $_REQUEST = $_POST = [];
        SugarTestHelper::tearDown();
    }

    /**
     * Tests that 'create_new_list_query' creates query without duplicating
     * $vardef[id_name] column in select statement.
     */
    public function testListQuery()
    {
        $bean = BeanFactory::newBean('Accounts');
        $query = $bean->create_new_list_query(
            'accounts.name',
            "(accounts.name like 'A%')",
            [],
            [],
            0,
            '',
            true,
            null,
            false
        );
        $this->assertEquals(1, substr_count($query['select'], 'accounts_cstm.contact_id_c'));
    }
}
