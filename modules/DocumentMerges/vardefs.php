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
$dictionary['DocumentMerge'] = [
    'table' => 'document_merges',
    'archive' => false,
    'audited' => true,
    'activity_enabled' => true,
    'full_text_search' => true,
    'unified_search_default_enabled' => true,
    'duplicate_merge' => true,
    'comment' => 'Document Merges are used with Document Merging',
    'fields' => [
        'parent_name' => [
            'required' => false,
            'source' => 'non-db',
            'name' => 'parent_name',
            'vname' => 'LBL_RECORD',
            'type' => 'parent',
            'massupdate' => false,
            'default' => '',
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'enabled',
            'duplicate_merge_dom_value' => '1',
            'audited' => false,
            'reportable' => true,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'len' => 36,
            'size' => '20',
            'options' => 'parent_type_display',
            'type_name' => 'parent_type',
            'id_name' => 'parent_id',
            'parent_type' => 'record_type_display',
            'studio' => 'visible',
            'readonly' => true,
        ],
        'parent_type' => [
            'required' => false,
            'name' => 'parent_type',
            'vname' => 'LBL_PARENT_TYPE',
            'type' => 'parent_type',
            'massupdate' => false,
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'enabled',
            'duplicate_merge_dom_value' => 1,
            'audited' => false,
            'reportable' => true,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'len' => 255,
            'size' => '20',
            'dbType' => 'varchar',
            'studio' => 'hidden',
            'group' => 'parent_name',
            'readonly' => true,
        ],
        'parent_id' => [
            'required' => false,
            'name' => 'parent_id',
            'vname' => 'LBL_PARENT_ID',
            'type' => 'id',
            'massupdate' => false,
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'enabled',
            'duplicate_merge_dom_value' => 1,
            'audited' => false,
            'reportable' => true,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'readonly' => true,
        ],
        'template' => [
            'required' => false,
            'source' => 'non-db',
            'name' => 'template',
            'vname' => 'LBL_TEMPLATE',
            'type' => 'relate',
            'calculated' => false,
            'len' => '255',
            'size' => '20',
            'id_name' => 'template_id',
            'module' => 'DocumentTemplates',
            'quicksearch' => 'enabled',
            'studio' => 'visible',
            'readonly' => true,
        ],
        'template_id' => [
            'required' => false,
            'name' => 'template_id',
            'vname' => 'LBL_TEMPLATE_ID',
            'type' => 'id',
            'massupdate' => false,
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'enabled',
            'duplicate_merge_dom_value' => 1,
            'audited' => false,
            'reportable' => true,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'readonly' => true,
        ],
        'generated_document_id' => [
            'required' => false,
            'name' => 'generated_document_id',
            'vname' => 'LBL_GENERATED_DOCUMENT_ID',
            'type' => 'id',
            'massupdate' => false,
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'enabled',
            'duplicate_merge_dom_value' => 1,
            'audited' => false,
            'reportable' => true,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'readonly' => true,
        ],
        'status' => [
            'required' => false,
            'name' => 'status',
            'vname' => 'LBL_STATUS',
            'type' => 'enum',
            'massupdate' => true,
            'default' => 'processing',
            'no_default' => false,
            'importable' => 'true',
            'calculated' => false,
            'len' => 100,
            'size' => '20',
            'options' => 'merge_status_list',
            'readonly' => true,
        ],
        'merge_type' => [
            'required' => false,
            'name' => 'merge_type',
            'vname' => 'LBL_MERGE_TYPE',
            'type' => 'enum',
            'massupdate' => true,
            'default' => 'merge',
            'no_default' => false,
            'importable' => 'true',
            'calculated' => false,
            'len' => 100,
            'size' => '20',
            'options' => 'merge_type_list',
            'readonly' => true,
        ],
        'file_type' => [
            'required' => false,
            'name' => 'file_type',
            'vname' => 'LBL_FILE_TYPE',
            'type' => 'enum',
            'massupdate' => true,
            'default' => 'DOC',
            'no_default' => false,
            'importable' => 'true',
            'calculated' => false,
            'len' => 100,
            'size' => '20',
            'options' => 'file_type_list',
            'readonly' => true,
        ],
        'message' => [
            'name' => 'message',
            'vname' => 'LBL_MESSAGE',
            'type' => 'varchar',
            'dbType' => 'varchar',
            'required' => false,
            'len' => '255',
            'readonly' => true,
        ],
        'seen' => [
            'name' => 'seen',
            'label' => 'LBL_SEEN',
            'type' => 'bool',
            'default_value' => false,
            'mass_update' => false,
            'importable' => 'true',
        ],
        'dismissed' => [
            'required' => false,
            'name' => 'dismissed',
            'vname' => 'LBL_DISMISSED',
            'type' => 'bool',
            'default' => false,
        ],
        'record_ids' => [
            'name' => 'record_ids',
            'vname' => 'LBL_RECORD_IDS',
            'type' => 'multimerge-records',
            'dbtype' => 'text',
            'comment' => 'store the record ids for the multimerge',
            'rows' => 6,
            'cols' => 80,
            'duplicate_on_record_copy' => 'always',
            'readonly' => true,
        ],
        'flow_data' => [
            'name' => 'flow_data',
            'type' => 'text',
            'dbtype' => 'longtext',
            'help' => 'Flow Data used for bpm',
        ],
    ],
    'duplicate_check' => [
        'enabled' => false,
    ],
];

VardefManager::createVardef('DocumentMerges', 'DocumentMerge', ['basic', 'assignable', 'team_security']);
