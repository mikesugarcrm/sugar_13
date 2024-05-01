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

/**
 * THIS CLASS IS GENERATED BY MODULE BUILDER
 * PLEASE DO NOT CHANGE THIS CLASS
 * PLACE ANY CUSTOMIZATIONS IN pmse_BpmFlow
 */
class pmse_BpmFlow_sugar extends Basic
{
    public $new_schema = true;
    public $module_dir = 'pmse_Project/pmse_BpmFlow';
    public $module_name = 'pmse_BpmFlow';
    public $object_name = 'pmse_BpmFlow';
    public $table_name = 'pmse_bpm_flow';
    public $importable = false;
    public $disable_custom_fields = true;
    public $id;
    public $name;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $modified_by_name;
    public $created_by;
    public $created_by_name;
    public $description;
    public $deleted;
    public $created_by_link;
    public $modified_user_link;
    public $activities;
    public $assigned_user_id;
    public $assigned_user_name;
    public $assigned_user_link;
    public $cas_id;
    public $cas_index;
    public $pro_id;
    public $cas_previous;
    public $cas_reassign_level;
    public $bpmn_id;
    public $bpmn_type;
    public $cas_user_id;
    public $cas_thread;
    public $cas_flow_status;
    public $cas_sugar_module;
    public $cas_sugar_object_id;
    public $cas_sugar_action;
    public $cas_adhoc_type;
    public $cas_adhoc_parent_id;
    public $cas_task_start_date;
    public $cas_delegate_date;
    public $cas_start_date;
    public $cas_finish_date;
    public $cas_due_date;
    public $cas_queue_duration;
    public $cas_duration;
    public $cas_delay_duration;
    public $cas_started;
    public $cas_finished;
    public $cas_delayed;


    public function __construct()
    {
        parent::__construct();
    }
}