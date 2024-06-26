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


$dictionary['PdfManager'] = [
    'table' => 'pdfmanager',
    'favorites' => false,
    'audited' => false,
    'duplicate_merge' => true,
    'fields' => [
        'base_module' => [
            'required' => true,
            'name' => 'base_module',
            'vname' => 'LBL_BASE_MODULE',
            'type' => 'enum',
            'massupdate' => 0,
            'default' => '',
            'comments' => '',
            'help' => '',
            'function' => 'getPdfManagerAvailableModules',
            'importable' => 'false',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'len' => 100,
            'size' => '20',
            'options' => 'moduleList',
            'studio' => false,
            'dependency' => false,
        ],
        'published' => [
            'required' => false,
            'name' => 'published',
            'vname' => 'LBL_PUBLISHED',
            'type' => 'enum',
            'massupdate' => 0,
            'default' => 'yes',
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'len' => 100,
            'size' => '20',
            'options' => 'pdfmanager_yes_no_list',
            'studio' => false,
            'dependency' => false,
        ],
        'field' => [
            'required' => false,
            'name' => 'field',
            'vname' => 'LBL_FIELD',
            'type' => 'enum',
            'massupdate' => 0,
            'default' => '0',
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'len' => 100,
            'size' => '20',
            'options' => 'moduleList',
            'studio' => false,
            'dependency' => false,
        ],
        'body_html' => [
            'required' => false,
            'name' => 'body_html',
            'vname' => 'LBL_BODY_HTML',
            'type' => 'text',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'size' => '20',
            'studio' => false,
            'rows' => '4',
            'cols' => '20',
            'dependency' => false,
        ],
        'template_name' => [
            'required' => false,
            'name' => 'template_name',
            'vname' => 'LBL_TEMPLATE_NAME',
            'type' => 'varchar',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'len' => '255',
            'size' => '20',
        ],
        'author' => [
            'required' => true,
            'name' => 'author',
            'vname' => 'LBL_AUTHOR',
            'default' => 'SugarCRM',
            'type' => 'varchar',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'len' => '255',
            'size' => '20',
        ],
        'title' => [
            'required' => false,
            'name' => 'title',
            'vname' => 'LBL_TITLE',
            'type' => 'varchar',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'len' => '255',
            'size' => '20',
        ],
        'subject' => [
            'required' => false,
            'name' => 'subject',
            'vname' => 'LBL_SUBJECT',
            'type' => 'varchar',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'len' => '255',
            'size' => '20',
        ],
        'keywords' => [
            'required' => false,
            'name' => 'keywords',
            'vname' => 'LBL_KEYWORDS',
            'type' => 'varchar',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'len' => '255',
            'size' => '20',
        ],
        'header_title' => [
            'required' => false,
            'name' => 'header_title',
            'vname' => 'LBL_HEADER_TITLE',
            'type' => 'varchar',
            'massupdate' => 0,
            'comments' => 'PDF header title',
            'help' => 'Header title',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'len' => '255',
            'size' => '20',
        ],
        'header_text' => [
            'required' => false,
            'name' => 'header_text',
            'vname' => 'LBL_HEADER_TEXT',
            'type' => 'varchar',
            'massupdate' => 0,
            'comments' => 'PDF header text',
            'help' => 'Header text',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'len' => '255',
            'size' => '20',
        ],
        'header_logo' => [
            'name' => 'header_logo',
            'vname' => 'LBL_HEADER_LOGO_FILE',
            'type' => 'file',
            'dbType' => 'varchar',
            'len' => 255,
            'comment' => 'PDF header logo',
            'help' => 'PDF header logo',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'importable' => false,
        ],
        'footer_text' => [
            'required' => false,
            'name' => 'footer_text',
            'vname' => 'LBL_FOOTER_TEXT',
            'type' => 'varchar',
            'massupdate' => 0,
            'comments' => 'PDF footer text',
            'help' => 'Footer text',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'len' => '255',
            'size' => '20',
        ],
    ],
    'acls' => [
        'SugarACLAdminOnly' => [
            'allowUserRead' => true,
        ],
    ],
    'indices' => [
        ['name' => 'idx_pdfmanager_base_module', 'type' => 'index', 'fields' => ['base_module']],
        ['name' => 'idx_pdfmanager_published', 'type' => 'index', 'fields' => ['published']],
    ],
    'relationships' => [],
    'optimistic_locking' => true,

];

VardefManager::createVardef('PdfManager', 'PdfManager', ['basic', 'team_security', 'assignable']);
