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
require_once 'data/Relationships/SugarRelationship.php';

/**
 * @ticket 62026
 * @author avucinic
 */
class Bug62026Test extends TestCase
{
    private static $custom_field_def = [
        'calculated' => 'true',
        'formula' => 'count($tasks)',
        'name' => 'bug62026',
        'type' => 'text',
        'enforced' => 'true',
        'label' => 'Bug 62026 Custom Count',
        'module' => 'ModuleBuilder',
        'view_module' => 'Accounts',
    ];

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', [true, 1]);

        // Create the custom field
        $mbc = new ModuleBuilderController();
        $_REQUEST = self::$custom_field_def;
        $mbc->action_saveField();

        // Update field name, all custom field have _c appended
        self::$custom_field_def['name'] .= '_c';

        // Refresh vardefs (for related_calc_fields)
        VardefManager::refreshVardefs('Accounts', 'Account');
        VardefManager::refreshVardefs('Tasks', 'Task');
    }

    public static function tearDownAfterClass(): void
    {
        $mbc = new ModuleBuilderController();

        // Delete the custom field
        $_REQUEST = self::$custom_field_def;
        $mbc->action_DeleteField();

        // Clear created beans
        SugarTestTaskUtilities::removeAllCreatedTasks();
        SugarTestAccountUtilities::removeAllCreatedAccounts();

        $_REQUEST = [];
        SugarCache::$isCacheReset = false;

        SugarTestHelper::tearDown();
    }

    public function testCalculatedFieldCount()
    {
        // Create account
        $account = SugarTestAccountUtilities::createAccount();
        $account = $account->retrieve($account->id);
        // Create task
        $task = new Task();

        // Pick the custom field name for usage
        $field = self::$custom_field_def['name'];

        // Link to account when creating new task using parent_type/id
        $task->name = 'Bug 62026';
        $task->parent_id = $account->id;
        $task->parent_name = $account->name;
        $task->parent_type = $account->module_dir;
        $task->save();
        $account = $account->retrieve($account->id);
        $this->assertEquals(1, $account->$field, 'Create account with parent_id/type does not update count()');

        // Save the ID for removal after test is done
        SugarTestTaskUtilities::setCreatedTask([$task->id]);

        // Unlink account by updating parent_id/type
        $task = $task->retrieve($task->id);
        $task->parent_id = '';
        $task->parent_type = 'Leads';
        $task->save();
        $account = $account->retrieve($account->id);
        $this->assertEquals(0, $account->$field, 'Unlink account by blanking parent_id/type does not update count()');

        // Load accounts relationship
        $task->load_relationship('accounts');

        // Check linking task with account
        $task->accounts->add($account);
        SugarRelationship::resaveRelatedBeans();
        $account = $account->retrieve($account->id);
        $this->assertEquals(1, $account->$field, 'Add relationship does not update count()');

        // Check unlinking task with account
        $task->accounts->delete($task->id, $account);
        SugarRelationship::resaveRelatedBeans();
        $account = $account->retrieve($account->id);
        $this->assertEquals(0, $account->$field, 'Delete relationship does not update count()');

        // Link to account by updating parent_id/type
        $task = $task->retrieve($task->id);
        $task->parent_id = $account->id;
        $task->parent_type = $account->module_dir;
        $task->save();
        $account = $account->retrieve($account->id);
        $this->assertEquals(1, $account->$field, 'Update parent_id/type does not update count()');

        // Check deletion of task
        $task->retrieve($task->id);
        $task->mark_deleted($task->id);
        $account = $account->retrieve($account->id);
        $this->assertEquals(0, $account->$field, 'Bean delete does not update count()');
    }
}
