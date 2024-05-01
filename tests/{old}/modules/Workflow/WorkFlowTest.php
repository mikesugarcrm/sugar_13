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

class WorkFlowTest extends TestCase
{
    /**
     * @var \WorkFlow|mixed
     */
    public $wf;
    protected $testWFName = 'WFUnitTest';
    protected $testValue = 'Workflow triggred!';
    protected $testAccName = 'WF Test Account';
    private static $has_workflow_directory;
    private static $has_logic_hooks_file;
    private static $wf_files = ['actions_array.php', 'alerts_array.php', 'plugins_array.php', 'triggers_array.php', 'workflow.php'];
    private $createdIds;

    public static function setUpBeforeClass(): void
    {
        if (file_exists('custom/modules/Accounts/workflow')) {
            self::$has_workflow_directory = true;
        } else {
            mkdir_recursive('custom/modules/Accounts/workflow');
        }

        foreach (self::$wf_files as $file) {
            $target_file = 'custom/modules/Accounts/workflow/' . $file;
            if (file_exists($target_file)) {
                copy($target_file, $target_file . '.bak');
            }

            $test_file = 'tests/{old}/include/workflow/testfiles/workflow/' . $file;
            if (file_exists($test_file)) {
                copy($test_file, $target_file);
            }
        }

        if (file_exists('custom/modules/Accounts/logic_hooks.php')) {
            self::$has_logic_hooks_file = true;
            copy('custom/modules/Accounts/logic_hooks.php', 'custom/modules/Accounts/logic_hooks.php.bak');
        }
        copy('tests/{old}/include/workflow/testfiles/logic_hooks.php', 'custom/modules/Accounts/logic_hooks.php');
        LogicHook::refreshHooks();
    }

    protected function setUp(): void
    {
        $this->testWFName = 'WFUnitTest' . random_int(0, mt_getrandmax());
        $this->testAccName = 'WFTestAccount' . random_int(0, mt_getrandmax());
        $this->wf = new WorkFlow();
        $this->wf->name = $this->testWFName;
        $this->wf->base_module = 'Accounts';
        $this->wf->type = 'Normal';
        $this->wf->fire_order = 'alerts_actions';
        $this->wf->record_type = 'All';
        $this->wf->save();
        $this->createdIds = [$this->wf->id];
    }

    protected function tearDown(): void
    {
        $db = $GLOBALS['db'];
        $this->wf->deleted = true;
        $this->wf->mark_deleted($this->wf->id);
        foreach ($this->createdIds as &$id) {
            $id = $db->quoted($id);
        }
        $sql = 'DELETE FROM workflow WHERE id IN (' . implode(',', $this->createdIds) . ')';
        $db->query($sql);
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$has_workflow_directory) {
            foreach (self::$wf_files as $file) {
                $target_file = 'custom/modules/Accounts/workflow/' . $file;
                if (file_exists($target_file . '.bak')) {
                    copy($target_file . '.bak', $target_file);
                    unlink($target_file . '.bak');
                } else {
                    unlink($target_file);
                }
            }
        } else {
            rmdir_recursive('custom/modules/Accounts/workflow');
        }

        if (self::$has_logic_hooks_file) {
            copy('custom/modules/Accounts/logic_hooks.php.bak', 'custom/modules/Accounts/logic_hooks.php');
            unlink('custom/modules/Accounts/logic_hooks.php.bak');
        } else {
            unlink('custom/modules/Accounts/logic_hooks.php');
        }
    }

    public function testCreate_new_list_query()
    {
        $query = $this->wf->create_new_list_query('name', "workflow.name like '{$this->testWFName}%'");
        $result = $this->wf->db->query($query);
        $count = 0;
        while ($row = $this->wf->db->fetchByAssoc($result)) {
            $count++;
        }
        $this->assertEquals(1, $count);
    }

    public function testDeleteWorkFlowsByModule()
    {
        $ids = [];
        foreach (['Contacts', 'Accounts', 'Meetings'] as $moduleName) {
            $ids[$moduleName] = [];
            for ($i = 0; $i < 5; $i++) {
                $wf = new WorkFlow();
                $wf->name = $this->testWFName;
                $wf->base_module = $moduleName;
                $wf->type = 'Normal';
                $wf->save();
                $ids[$moduleName][] = $wf->id;
                $this->createdIds[] = $wf->id;
            }
        }
        WorkFlow::deleteWorkFlowsByModule(['Contacts', 'Meetings']);
        foreach (['Contacts', 'Meetings'] as $moduleName) {
            foreach ($ids[$moduleName] as $id) {
                $wf = BeanFactory::retrieveBean('WorkFlow', $id, [], false);
                $this->assertEquals(1, $wf->deleted);
            }
        }
        foreach ($ids['Accounts'] as $id) {
            $wf = BeanFactory::retrieveBean('WorkFlow', $id);
            $this->assertEquals(0, $wf->deleted);
        }
    }

    /* Non-functional test.
    public function testWrite_workflow()
    {
        //Build the workflow components
        echo ("Building workflow trigger...\n");
        $trigger = new WorkFlowTriggerShell();
        $trigger->type = "trigger_record_change";
        $trigger->frame_type = "Primary";
        $trigger->rel_module_type = "any";
        $trigger->parent_id = $this->wf->id;
        $trigger->save();

        echo ("Building workflow Action Shell...\n");
        $actionShell = new WorkFlowActionShell();
        $actionShell->action_type = "update";
        $actionShell->rel_module_type = "all";
        $actionShell->parent_id = $this->wf->id;
        $actionShell->save();

        echo ("Building workflow Action...\n");
        $action = new WorkFlowAction();
        $action->field = "description";
        $action->value = $this->testValue;
        $action->set_type = "Basic";
        $action->parent_id = $actionShell->id;
        $action->save();

        echo ("Rebuilding workflow...\n");
        //Now build the logic hook and test it
        $this->wf->check_logic_hook_file();
        $this->wf->write_workflow();

        echo ("Creating a new Account...w\n");
        $acc = new Account();
        $acc->name = $this->testAccName;
        $acc->save();

        $this->assertEquals($this->testValue, $acc->description);
    }
    */
}
