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


$dictionary['project_task_project_tasks'] = [
    'table' => 'project_task_project_tasks',
    'fields' => [
        'id' => [
            'name' => 'id',
            'vname' => 'LBL_ID',
            'required' => true,
            'type' => 'id',
        ],
        'project_task_id' => [
            'name' => 'project_task_id',
            'vname' => 'LBL_PROJECT_TASK_ID',
            'required' => true,
            'type' => 'id',
        ],
        'predecessor_project_task_id' => [
            'name' => 'predecessor_project_task_id',
            'vname' => 'LBL_PROJECT_TASK_ID',
            'required' => true,
            'type' => 'id',
        ],
        'deleted' => [
            'name' => 'deleted',
            'vname' => 'LBL_DELETED',
            'type' => 'bool',
            'required' => false,
            'default' => '0',
        ],
    ],
    'indices' => [
        [
            'name' => 'proj_rel_pk',
            'type' => 'primary',
            'fields' => ['id'],
        ],
    ],

    'relationships' => [
        'project_task_project_tasks' => [
            'lhs_module' => 'ProjectTasks2',
            'lhs_table' => 'project_tasks',
            'lhs_key' => 'id',
            'rhs_module' => 'ProjectTasks2',
            'rhs_table' => 'project_tasks',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'project_task_project_tasks',
            'join_key_lhs' => 'project_task_id',
            'join_key_rhs' => 'predecessor_project_task_id',
        ],
    ],
];
