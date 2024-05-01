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

$dictionary['Document'] = ['table' => 'documents'
    , 'fields' => [

        'document_name' => [
            'name' => 'document_name',
            'vname' => 'LBL_NAME',
            'type' => 'varchar',
            'len' => '255',
            'required' => true,
            'importable' => 'required',
        ],
        'name' => ['name' => 'name', 'vname' => 'LBL_NAME', 'source' => 'non-db', 'type' => 'varchar'],
        'filename' => [
            'name' => 'filename',
            'vname' => 'LBL_FILENAME',
            'type' => 'file',
            'source' => 'non-db',
            'comment' => 'The filename of the document attachment',
            'required' => true,
        ],

        'uploadfile' => [
            'name' => 'uploadfile',
            'required' => true,
            'vname' => 'LBL_FILENAME',
            'type' => 'file',
            'source' => 'non-db',
        ],

        'active_date' => [
            'name' => 'active_date',
            'vname' => 'LBL_DOC_ACTIVE_DATE',
            'type' => 'date',
            'importable' => 'required',
            'required' => true,
        ],

        'exp_date' => [
            'name' => 'exp_date',
            'vname' => 'LBL_DOC_EXP_DATE',
            'type' => 'date',
        ],

        'category_id' => [
            'name' => 'category_id',
            'vname' => 'LBL_SF_CATEGORY',
            'type' => 'enum',
            'len' => 100,
            'options' => 'document_category_dom',
            'reportable' => false,
        ],

        'subcategory_id' => [
            'name' => 'subcategory_id',
            'vname' => 'LBL_SF_SUBCATEGORY',
            'type' => 'enum',
            'len' => 100,
            'options' => 'document_subcategory_dom',
            'reportable' => false,
        ],

        'status_id' => [
            'name' => 'status_id',
            'vname' => 'LBL_DOC_STATUS',
            'type' => 'enum',
            'len' => 100,
            'options' => 'document_status_dom',
            'reportable' => false,
        ],

        'status' => [
            'name' => 'status',
            'vname' => 'LBL_DOC_STATUS',
            'type' => 'varchar',
            'source' => 'non-db',
            'comment' => 'Document status for Meta-Data framework',
        ],

        'document_revision_id' => [
            'name' => 'document_revision_id',
            'vname' => 'LBL_LATEST_REVISION',
            'type' => 'varchar',
            'len' => '36',
            'reportable' => false,
        ],

        'revisions' => [
            'name' => 'revisions',
            'type' => 'link',
            'relationship' => 'document_revisions',
            'source' => 'non-db',
            'vname' => 'LBL_REVISIONS',
        ],

        'revision' => [
            'name' => 'revision',
            'vname' => 'LBL_DOC_VERSION',
            'type' => 'varchar',
            'reportable' => false,
            'required' => true,
            'source' => 'non-db',
            'importable' => 'required',
            'required' => true,
        ],

        'last_rev_created_name' => [
            'name' => 'last_rev_created_name',
            'vname' => 'LBL_LAST_REV_CREATOR',
            'type' => 'varchar',
            'reportable' => false,
            'source' => 'non-db',
        ],
        'last_rev_mime_type' => [
            'name' => 'last_rev_mime_type',
            'vname' => 'LBL_LAST_REV_MIME_TYPE',
            'type' => 'varchar',
            'reportable' => false,
            'studio' => 'false',
            'source' => 'non-db',
        ],
        'latest_revision' => [
            'name' => 'latest_revision',
            'vname' => 'LBL_LATEST_REVISION',
            'type' => 'varchar',
            'reportable' => false,
            'source' => 'non-db',
        ],
        'last_rev_create_date' => [
            'name' => 'last_rev_create_date',
            'type' => 'date',
            'table' => 'document_revisions',
            'link' => 'revisions',
            'join_name' => 'document_revisions',
            'vname' => 'LBL_LAST_REV_CREATE_DATE',
            'rname' => 'date_entered',
            'reportable' => false,
            'source' => 'non-db',
        ],
        'contracts' => [
            'name' => 'contracts',
            'type' => 'link',
            'relationship' => 'contracts_documents',
            'source' => 'non-db',
            'vname' => 'LBL_CONTRACTS',
        ],
        //todo remove
        'leads' => [
            'name' => 'leads',
            'type' => 'link',
            'relationship' => 'leads_documents',
            'source' => 'non-db',
            'vname' => 'LBL_LEADS',
        ],

        'related_doc_id' => [
            'name' => 'related_doc_id',
            'vname' => 'LBL_RELATED_DOCUMENT_ID',
            'reportable' => false,
            'dbType' => 'id',
            'type' => 'varchar',
            'len' => '36',
        ],

        'related_doc_name' => [
            'name' => 'related_doc_name',
            'vname' => 'LBL_DET_RELATED_DOCUMENT',
            'type' => 'relate',
            'table' => 'documents',
            'id_name' => 'related_doc_id',
            'module' => 'Documents',
            'source' => 'non-db',
            'comment' => 'The related document name for Meta-Data framework',
        ],

        'related_doc_rev_id' => [
            'name' => 'related_doc_rev_id',
            'vname' => 'LBL_RELATED_DOCUMENT_REVISION_ID',
            'reportable' => false,
            'dbType' => 'id',
            'type' => 'varchar',
            'len' => '36',
        ],

        'related_doc_rev_number' => [
            'name' => 'related_doc_rev_number',
            'vname' => 'LBL_DET_RELATED_DOCUMENT_VERSION',
            'type' => 'varchar',
            'source' => 'non-db',
            'comment' => 'The related document version number for Meta-Data framework',
        ],

        'is_template' => [
            'name' => 'is_template',
            'vname' => 'LBL_IS_TEMPLATE',
            'type' => 'bool',
            'default' => 0,
            'reportable' => false,
        ],
        'template_type' => [
            'name' => 'template_type',
            'vname' => 'LBL_TEMPLATE_TYPE',
            'type' => 'enum',
            'len' => 100,
            'options' => 'document_template_type_dom',
            'reportable' => false,
        ],
//BEGIN field used for contract document subpanel.
        'latest_revision_name' => [
            'name' => 'latest_revision_name',
            'vname' => 'LBL_LASTEST_REVISION_NAME',
            'type' => 'varchar',
            'reportable' => false,
            'source' => 'non-db',
        ],

        'selected_revision_name' => [
            'name' => 'selected_revision_name',
            'vname' => 'LBL_SELECTED_REVISION_NAME',
            'type' => 'varchar',
            'reportable' => false,
            'source' => 'non-db',
        ],
        'contract_status' => [
            'name' => 'contract_status',
            'vname' => 'LBL_CONTRACT_STATUS',
            'type' => 'varchar',
            'reportable' => false,
            'source' => 'non-db',
        ],
        'contract_name' => [
            'name' => 'contract_name',
            'vname' => 'LBL_CONTRACT_NAME',
            'type' => 'varchar',
            'reportable' => false,
            'source' => 'non-db',
        ],
        'linked_id' => [
            'name' => 'linked_id',
            'vname' => 'LBL_LINKED_ID',
            'type' => 'varchar',
            'reportable' => false,
            'source' => 'non-db',
        ],
        'selected_revision_id' => [
            'name' => 'selected_revision_id',
            'vname' => 'LBL_SELECTED_REVISION_ID',
            'type' => 'varchar',
            'reportable' => false,
            'source' => 'non-db',
        ],
        'latest_revision_id' => [
            'name' => 'latest_revision_id',
            'vname' => 'LBL_LATEST_REVISION_ID',
            'type' => 'varchar',
            'reportable' => false,
            'source' => 'non-db',
        ],
        'selected_revision_filename' => [
            'name' => 'selected_revision_filename',
            'vname' => 'LBL_SELECTED_REVISION_FILENAME',
            'type' => 'varchar',
            'reportable' => false,
            'source' => 'non-db',
        ],
//END fields used for contract documents subpanel.

    ],
    'indices' => [
        ['name' => 'idx_doc_cat', 'type' => 'index', 'fields' => ['category_id', 'subcategory_id']],
    ],
    'relationships' => [
        'document_revisions' => ['lhs_module' => 'Documents', 'lhs_table' => 'documents', 'lhs_key' => 'id',
            'rhs_module' => 'Documents', 'rhs_table' => 'document_revisions', 'rhs_key' => 'document_id',
            'relationship_type' => 'one-to-many',
        ]

        , 'documents_modified_user' => ['lhs_module' => 'Users', 'lhs_table' => 'users', 'lhs_key' => 'id',
            'rhs_module' => 'Documents', 'rhs_table' => 'documents', 'rhs_key' => 'modified_user_id',
            'relationship_type' => 'one-to-many',
        ]

        , 'documents_created_by' => ['lhs_module' => 'Users', 'lhs_table' => 'users', 'lhs_key' => 'id',
            'rhs_module' => 'Documents', 'rhs_table' => 'documents', 'rhs_key' => 'created_by',
            'relationship_type' => 'one-to-many',
        ],
    ],

];
VardefManager::createVardef('Documents', 'Document', ['default',
    'team_security',
]);
