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
 * PLACE ANY CUSTOMIZATIONS IN pmse_BpmThread
 */
class pmse_BpmThread_sugar extends Basic
{
    public $new_schema = true;
    public $module_dir = 'pmse_Project/pmse_BpmThread';
    public $module_name = 'pmse_BpmThread';
    public $object_name = 'pmse_BpmThread';
    public $table_name = 'pmse_bpm_thread';
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
    public $cas_thread_index;
    public $cas_thread_parent;
    public $cas_thread_status;
    public $cas_flow_index;
    public $cas_thread_tokens;
    public $cas_thread_passes;


    public function __construct()
    {
        parent::__construct();
    }
}
