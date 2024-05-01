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

class WorkFlowBugsTest extends TestCase
{
    private static $has_workflow_directory;
    private static $has_logic_hooks_file;
    private static $wf_files = ['actions_array.php', 'alerts_array.php', 'plugins_array.php', 'triggers_array.php', 'workflow.php'];
    private $test_account;
    private $test_team1;
    private $test_team2;

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

        $sql = "DELETE FROM workflow where id in ('436cfc81-1926-5ba6-cfec-4c72d7b861c4', '43406320-49b6-6503-0074-4c73532a4325')";
        $GLOBALS['db']->query($sql);

        $sql = "DELETE FROM workflow_actionshells where id in ('abc28c1d-e47a-bb56-d1e3-4c72d75d8c9b', 'db7b84f8-6892-8ab2-7855-4c73549a48a1')";
        $GLOBALS['db']->query($sql);

        $sql = "DELETE FROM workflow_actions where id in ('b158427e-fa71-1727-4306-4c72d7034409', 'e48c9998-a394-4a13-1a52-4c7354b17f06')";
        $GLOBALS['db']->query($sql);

        $sql = "DELETE FROM workflow_triggershells where id in ('153c738b-3674-3db7-314e-4c72d7ea4eb9', '88809b43-e3fb-17fc-c311-4c735359cebe')";
        $GLOBALS['db']->query($sql);

        $sql = "UPDATE workflow set deleted = 1, status = 0 WHERE base_module = 'Accounts'";
        $GLOBALS['db']->query($sql);

        $sql = "INSERT INTO workflow(id, deleted, date_entered, date_modified, modified_user_id, created_by, name, base_module, status, type, fire_order, record_type, list_order_y)
        VALUES ('436cfc81-1926-5ba6-cfec-4c72d7b861c4', 0, '2010-08-23 20:18:04', '2010-08-23 20:18:04', '1', '1', 'Yo Yo!', 'Accounts', 1, 'Normal', 'alerts_actions', 'All', 0)";
        $GLOBALS['db']->query($sql);

        $sql = "INSERT INTO workflow(id, deleted, date_entered, date_modified, modified_user_id, created_by, name, base_module, status, type, fire_order, record_type, list_order_y)
        VALUES ('43406320-49b6-6503-0074-4c73532a4325', 0, '2010-08-23 20:18:04', '2010-08-23 20:18:04', '1', '1', 'Bug', 'Accounts', 1, 'Normal', 'alerts_actions', 'All', 1)";
        $GLOBALS['db']->query($sql);

        $sql = "INSERT INTO workflow_actionshells(id, deleted, date_entered, date_modified, modified_user_id, created_by, action_type, parent_id, rel_module_type)
        VALUES ('abc28c1d-e47a-bb56-d1e3-4c72d75d8c9b', 0, '2010-08-23 20:18:04', '2010-08-23 20:18:04', '1', '1', 'update', '436cfc81-1926-5ba6-cfec-4c72d7b861c4', 'all')";
        $GLOBALS['db']->query($sql);

        $sql = "INSERT INTO workflow_actionshells(id, deleted, date_entered, date_modified, modified_user_id, created_by, action_type, parent_id, rel_module_type)
        VALUES ('db7b84f8-6892-8ab2-7855-4c73549a48a1', 0, '2010-08-23 20:18:04', '2010-08-23 20:18:04', '1', '1', 'update', '43406320-49b6-6503-0074-4c73532a4325', 'all')";
        $GLOBALS['db']->query($sql);

        $sql = "INSERT INTO workflow_actions(id, deleted, date_entered, date_modified, modified_user_id, created_by, field, value, set_type, parent_id)
        VALUES ('b158427e-fa71-1727-4306-4c72d7034409', 0, '2010-08-23 20:18:04', '2010-08-23 20:18:04', '1', '1', 'description', 'Hey Man!', 'Basic', 'abc28c1d-e47a-bb56-d1e3-4c72d75d8c9b')";
        $GLOBALS['db']->query($sql);

        $sql = "INSERT INTO workflow_actions(id, deleted, date_entered, date_modified, modified_user_id, created_by, field, value, set_type, parent_id)
        VALUES ('e48c9998-a394-4a13-1a52-4c7354b17f06', 0, '2010-08-23 20:18:04', '2010-08-23 20:18:04', '1', '1', 'team_id', '1', 'Basic', 'db7b84f8-6892-8ab2-7855-4c73549a48a1')";
        $GLOBALS['db']->query($sql);

        $sql = "INSERT INTO workflow_triggershells(id, deleted, date_entered, date_modified, modified_user_id, created_by, type, frame_type, parent_id, show_past, rel_module_type)
        VALUES ('153c738b-3674-3db7-314e-4c72d7ea4eb9', 0, '2010-08-23 20:18:04', '2010-08-23 20:18:04', '1', '1', 'trigger_record_change', 'Primary', '436cfc81-1926-5ba6-cfec-4c72d7b861c4', 0, 'any')";
        $GLOBALS['db']->query($sql);

        $sql = "INSERT INTO workflow_triggershells(id, deleted, date_entered, date_modified, modified_user_id, created_by, field, type, frame_type, eval, parent_id, show_past, rel_module_type)
        VALUES ('88809b43-e3fb-17fc-c311-4c735359cebe', 0, '2010-08-23 20:18:04', '2010-08-23 20:18:04', '1', '1', 'name', 'compare_specific', 'Primary', '( !(\$focus->fetched_row[''name''] ==  ''Sugar'' )) && (isset(\$focus->name) && \$focus->name ==  ''Sugar'')', '43406320-49b6-6503-0074-4c73532a4325', 0, 'any')";
        $GLOBALS['db']->query($sql);

        $beanList = [];
        $beanFiles = [];
        require 'include/modules.php';
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        unset($GLOBALS['triggeredWorkflows']);
    }

    protected function setUp(): void
    {
        $this->test_team1 = SugarTestTeamUtilities::createAnonymousTeam();
        $this->test_team2 = SugarTestTeamUtilities::createAnonymousTeam();

        $this->test_account = new Account();
        $this->test_account->name = 'bug_32738_test';
        $this->test_account->team_id = $this->test_team1->id;
        $this->test_account->team_set_id = $this->test_team1->id;
        $this->test_account->save();
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

        $sql = "DELETE FROM workflow where id in ('436cfc81-1926-5ba6-cfec-4c72d7b861c4', '43406320-49b6-6503-0074-4c73532a4325')";
        $GLOBALS['db']->query($sql);

        $sql = "UPDATE workflow set deleted = 0, status = 1 WHERE base_module = 'Accounts'";
        $GLOBALS['db']->query($sql);

        $sql = "DELETE FROM workflow_actionshells where id in ('abc28c1d-e47a-bb56-d1e3-4c72d75d8c9b', 'db7b84f8-6892-8ab2-7855-4c73549a48a1')";
        $GLOBALS['db']->query($sql);

        $sql = "DELETE FROM workflow_actions where id in ('b158427e-fa71-1727-4306-4c72d7034409', 'e48c9998-a394-4a13-1a52-4c7354b17f06')";
        $GLOBALS['db']->query($sql);

        $sql = "DELETE FROM workflow_triggershells where id in ('153c738b-3674-3db7-314e-4c72d7ea4eb9', '88809b43-e3fb-17fc-c311-4c735359cebe')";
        $GLOBALS['db']->query($sql);

        unset($GLOBALS['beanList']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    protected function tearDown(): void
    {
        $sql = "DELETE FROM accounts WHERE id = '{$this->test_account->id}'";
        $GLOBALS['db']->query($sql);
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
    }

    /**
     * @ticket 32738
     */
    public function testBug32738()
    {
        $this->test_account->name = 'Sugar';
        $this->test_account->save();
        $this->assertEquals('1', $this->test_account->team_id);
    }

    /**
     * @ticket 38859
     */
    public function testBug38859()
    {
        $this->test_account->description = 'Hey Lady!';
        $this->test_account->team_id = $this->test_team2->id;
        $this->test_account->team_set_id = $this->test_team2->id;
        $this->test_account->save();
        //Assert that the description was changed by the workflow
        $this->assertEquals('Hey Man!', $this->test_account->description);
        //Assert that the team_id change was preserved
        $this->assertEquals($this->test_team2->id, $this->test_account->team_id);
        //Assert that the team_set_id change was preserved
        $this->assertEquals($this->test_team2->id, $this->test_account->team_set_id);
    }
}
