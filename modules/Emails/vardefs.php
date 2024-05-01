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

$dictionary['Email'] = [
    'favorites' => true,
    'table' => 'emails',
    'color' => 'ocean',
    'icon' => 'sicon-email-lg',
    'acls' => [
        'SugarACLEmails' => true,
        'SugarACLDraftEmails' => true,
        'SugarACLArchivedEmails' => true,
    ],
    'full_text_search' => true,
    'activity_enabled' => true,
    'comment' => 'Contains a record of emails sent to and from the Sugar application',
    'fields' => [
        'id' => [
            'name' => 'id',
            'vname' => 'LBL_ID',
            'type' => 'id',
            'required' => true,
            'reportable' => true,
            'comment' => 'Unique identifier',
        ],
        'date_entered' => [
            'name' => 'date_entered',
            'vname' => 'LBL_DATE_ENTERED',
            'type' => 'datetime',
            'required' => true,
            'comment' => 'Date record created',
            'readonly' => true,
            'massupdate' => false,
            'duplicate_on_record_copy' => 'no',
            'full_text_search' => [
                'enabled' => true,
                'searchable' => false,
                // Disabled until UX component is available
                //'aggregations' => array(
                //    'date_entered' => array(
                //        'type' => 'DateRange',
                //    ),
                //),
            ],
        ],
        'date_modified' => [
            'name' => 'date_modified',
            'vname' => 'LBL_DATE_MODIFIED',
            'type' => 'datetime',
            'required' => true,
            'comment' => 'Date record last modified',
            'readonly' => true,
            'massupdate' => false,
            'duplicate_on_record_copy' => 'no',
            'full_text_search' => [
                'enabled' => true,
                'searchable' => false,
                // Disabled until UX component is available
                //'aggregations' => array(
                //    'date_modified' => array(
                //        'type' => 'DateRange',
                //    ),
                //),
            ],
        ],
        'assigned_user_id' => [
            'name' => 'assigned_user_id',
            'vname' => 'LBL_ASSIGNED_TO',
            'type' => 'id',
            'isnull' => false,
            'reportable' => false,
            'comment' => 'User ID that last modified record',
            'full_text_search' => [
                'enabled' => true,
                'searchable' => false,
                'aggregations' => [
                    'assigned_user_id' => [
                        'type' => 'MyItems',
                        'label' => 'LBL_AGG_ASSIGNED_TO_ME',
                    ],
                ],
            ],
        ],
        'assigned_user_name' => [
            'name' => 'assigned_user_name',
            'id_name' => 'assigned_user_id',
            'vname' => 'LBL_ASSIGNED_TO',
            'link' => 'assigned_user_link',
            'rname' => 'full_name',
            'type' => 'relate',
            'reportable' => false,
            'source' => 'non-db',
            'table' => 'users',
            'module' => 'Users',
        ],
        'modified_user_id' => [
            'name' => 'modified_user_id',
            'rname' => 'user_name',
            'id_name' => 'modified_user_id',
            'vname' => 'LBL_MODIFIED_BY',
            'type' => 'assigned_user_name',
            'table' => 'users',
            'isnull' => false,
            'reportable' => true,
            'dbType' => 'id',
            'comment' => 'User ID that last modified record',
            'readonly' => true,
            'massupdate' => false,
            'duplicate_on_record_copy' => 'no',
            'full_text_search' => [
                'enabled' => true,
                'searchable' => false,
                'type' => 'id',
                'aggregations' => [
                    'modified_user_id' => [
                        'type' => 'MyItems',
                        'label' => 'LBL_AGG_MODIFIED_BY_ME',
                    ],
                ],
            ],
        ],
        'modified_by_name' => [
            'name' => 'modified_by_name',
            'vname' => 'LBL_MODIFIED_NAME',
            'type' => 'relate',
            'reportable' => false,
            'source' => 'non-db',
            'rname' => 'full_name',
            'table' => 'users',
            'id_name' => 'modified_user_id',
            'module' => 'Users',
            'link' => 'modified_user_link',
            'duplicate_merge' => 'disabled',
            'massupdate' => false,
        ],
        'created_by' => [
            'name' => 'created_by',
            'vname' => 'LBL_CREATED_BY',
            'type' => 'id',
            'reportable' => false,
            'comment' => 'User name who created record',
            'readonly' => true,
            'massupdate' => false,
            'duplicate_on_record_copy' => 'no',
            'full_text_search' => [
                'enabled' => true,
                'searchable' => false,
                'type' => 'id',
                'aggregations' => [
                    'created_by' => [
                        'type' => 'MyItems',
                        'label' => 'LBL_AGG_CREATED_BY_ME',
                    ],
                ],
            ],
        ],
        'created_by_name' => [
            'name' => 'created_by_name',
            'vname' => 'LBL_CREATED',
            'type' => 'relate',
            'reportable' => false,
            'link' => 'created_by_link',
            'rname' => 'full_name',
            'source' => 'non-db',
            'table' => 'users',
            'id_name' => 'created_by',
            'module' => 'Users',
            'duplicate_merge' => 'disabled',
            'importable' => false,
            'massupdate' => false,
        ],
        'deleted' => [
            'name' => 'deleted',
            'vname' => 'LBL_DELETED',
            'type' => 'bool',
            'required' => false,
            'reportable' => false,
            'comment' => 'Record deletion indicator',
        ],
        'from_addr_name' => [
            'name' => 'from_addr_name',
            'type' => 'varchar',
            'vname' => 'LBL_FROM',
            'source' => 'non-db',
            'full_text_search' => [
                'enabled' => true,
                'searchable' => false,
            ],
            'massupdate' => false,
        ],
        'reply_to_addr' => [
            'name' => 'reply_to_addr',
            'type' => 'varchar',
            'vname' => 'reply_to_addr',
            'source' => 'non-db',
            'massupdate' => false,
        ],
        'to_addrs_names' => [
            'name' => 'to_addrs_names',
            'type' => 'varchar',
            'vname' => 'LBL_TO_ADDRS',
            'source' => 'non-db',
            'reportable' => false,
            'massupdate' => false,
        ],
        'cc_addrs_names' => [
            'name' => 'cc_addrs_names',
            'type' => 'varchar',
            'vname' => 'LBL_CC',
            'source' => 'non-db',
            'reportable' => false,
            'massupdate' => false,
        ],
        'bcc_addrs_names' => [
            'name' => 'bcc_addrs_names',
            'type' => 'varchar',
            'vname' => 'LBL_BCC',
            'source' => 'non-db',
            'reportable' => false,
            'massupdate' => false,
        ],
        'raw_source' => [
            'name' => 'raw_source',
            'type' => 'varchar',
            'vname' => 'raw_source',
            'source' => 'non-db',
            'massupdate' => false,
        ],
        'description_html' => [
            'name' => 'description_html',
            'type' => 'htmleditable_tinymce',
            'vname' => 'description_html',
            'source' => 'non-db',
            'massupdate' => false,
            'full_text_search' => [
                'enabled' => false,
                'searchable' => false,
                'type' => 'text',
            ],
        ],
        'description' => [
            'name' => 'description',
            'type' => 'varchar',
            'vname' => 'LBL_TEXT_BODY',
            'source' => 'non-db',
            'full_text_search' => [
                'enabled' => false,
                'searchable' => false,
                'type' => 'text',
            ],
            'massupdate' => false,
        ],
        'date_sent' => [
            'name' => 'date_sent',
            'vname' => 'LBL_DATE_SENT',
            'type' => 'datetime',
            'massupdate' => false,
            'full_text_search' => [
                'enabled' => true,
                'searchable' => false,
            ],
            'hideacl' => true,
        ],
        'message_id' => [
            'name' => 'message_id',
            'vname' => 'LBL_MESSAGE_ID',
            'type' => 'varchar',
            'len' => 255,
            'comment' => 'ID of the email item obtained from the email transport system',
            'hideacl' => true,
            'massupdate' => false,
            'duplicate_on_record_copy' => 'no',
        ],
        // Bug #45395 : Deleted emails from a group inbox does not move the emails to the Trash folder for Google Apps
        'message_uid' => [
            'name' => 'message_uid',
            'vname' => 'LBL_MESSAGE_UID',
            'type' => 'varchar',
            'len' => 64,
            'comment' => 'UID of the email item obtained from the email transport system',
            'hideacl' => true,
            'massupdate' => false,
        ],
        'name' => [
            'name' => 'name',
            'vname' => 'LBL_SUBJECT',
            'type' => 'name',
            'dbType' => 'varchar',
            'required' => false,
            'len' => '255',
            'comment' => 'The subject of the email',
            'full_text_search' => [
                'enabled' => true,
                'searchable' => true,
            ],
            'hideacl' => true,
            'massupdate' => false,
        ],
        'type' => [
            'name' => 'type',
            'vname' => 'LBL_LIST_TYPE',
            'type' => 'enum',
            'options' => 'dom_email_types',
            'len' => 100,
            'massupdate' => false,
            'comment' => 'Type of email (ex: draft)',
            'hideacl' => true,
        ],
        'status' => [
            'name' => 'status',
            'vname' => 'LBL_STATUS',
            'type' => 'enum',
            'len' => 100,
            'options' => 'dom_email_status',
            'massupdate' => false,
            'hideacl' => true,
        ],
        'flagged' => [
            'name' => 'flagged',
            'vname' => 'LBL_EMAIL_FLAGGED',
            'type' => 'bool',
            'required' => false,
            'reportable' => false,
            'comment' => 'flagged status',
            'massupdate' => false,
        ],
        'reply_to_status' => [
            'name' => 'reply_to_status',
            'vname' => 'LBL_EMAIL_REPLY_TO_STATUS',
            'type' => 'bool',
            'required' => false,
            'reportable' => false,
            'comment' => 'If you reply to an email then reply to status of original email is set',
            'massupdate' => false,
            'hideacl' => true,
            'duplicate_on_record_copy' => 'no',
        ],
        'intent' => [
            'name' => 'intent',
            'vname' => 'LBL_INTENT',
            'type' => 'varchar',
            'len' => 100,
            'default' => 'pick',
            'comment' => 'Target of action used in Inbound Email assignment',
            'hideacl' => true,
        ],
        'mailbox_id' => [
            'name' => 'mailbox_id',
            'vname' => 'LBL_MAILBOX_ID',
            'type' => 'id',
            'reportable' => false,
            'massupdate' => false,
            'duplicate_on_record_copy' => 'no',
        ],
        'mailbox_name' => [
            'name' => 'mailbox_name',
            'rname' => 'name',
            'type' => 'relate',
            'source' => 'non-db',
            'vname' => 'LBL_MAILBOX',
            'reportable' => false,
            'id_name' => 'mailbox_id',
            'link' => 'mailbox',
            'module' => 'InboundEmail',
            'readonly' => true,
            'studio' => false,
        ],
        'mailbox' => [
            'name' => 'mailbox',
            'type' => 'link',
            'relationship' => 'inbound_email_emails',
            'source' => 'non-db',
            'vname' => 'LBL_MAILBOX',
        ],
        'created_by_link' => [
            'name' => 'created_by_link',
            'type' => 'link',
            'relationship' => 'emails_created_by',
            'vname' => 'LBL_CREATED_BY_USER',
            'link_type' => 'one',
            'module' => 'Users',
            'bean_name' => 'User',
            'source' => 'non-db',
        ],
        'modified_user_link' => [
            'name' => 'modified_user_link',
            'type' => 'link',
            'relationship' => 'emails_modified_user',
            'vname' => 'LBL_MODIFIED_BY_USER',
            'link_type' => 'one',
            'module' => 'Users',
            'bean_name' => 'User',
            'source' => 'non-db',
        ],
        'assigned_user_link' => [
            'name' => 'assigned_user_link',
            'type' => 'link',
            'relationship' => 'emails_assigned_user',
            'vname' => 'LBL_ASSIGNED_TO_USER',
            'link_type' => 'one',
            'module' => 'Users',
            'bean_name' => 'User',
            'source' => 'non-db',
        ],
        'state' => [
            'name' => 'state',
            'vname' => 'LBL_EMAIL_STATE',
            'type' => 'enum',
            'options' => 'dom_email_states',
            'len' => 100,
            'required' => true,
            'isnull' => false,
            'default' => 'Archived',
            'massupdate' => false,
            'comment' => 'An email is either a draft or archived',
            'reportable' => false,
            'hideacl' => true,
            'mandatory_fetch' => true,
        ],
        'reply_to_id' => [
            'name' => 'reply_to_id',
            'vname' => 'LBL_EMAIL_REPLY_TO_ID',
            'type' => 'id',
            'reportable' => false,
            'duplicate_on_record_copy' => 'no',
            'importable' => false,
            'comment' => 'Identifier of email record that this email was a reply to',
            'massupdate' => false,
        ],
        'parent_name' => [
            'name' => 'parent_name',
            'parent_type' => 'record_type_display',
            'type_name' => 'parent_type',
            'id_name' => 'parent_id',
            'vname' => 'LBL_LIST_RELATED_TO',
            'type' => 'parent',
            'group' => 'parent_name',
            'reportable' => false,
            'source' => 'non-db',
            'options' => 'parent_type_display',
        ],
        'parent_type' => [
            'name' => 'parent_type',
            'vname' => 'LBL_PARENT_TYPE',
            'type' => 'parent_type',
            'dbType' => 'varchar',
            'group' => 'parent_name',
            'options' => 'parent_type_display',
            'reportable' => false,
            'comment' => 'Identifier of Sugar module to which this email is associated',
        ],
        'parent_id' => [
            'name' => 'parent_id',
            'vname' => 'LBL_PARENT_ID',
            'type' => 'id',
            'group' => 'parent_name',
            'reportable' => false,
            'comment' => 'ID of Sugar object referenced by parent_type',
        ],
        'direction' => [
            'name' => 'direction',
            'vname' => 'LBL_EMAIL_DIRECTION',
            'type' => 'enum',
            'options' => 'dom_email_direction',
            'len' => 20,
            'required' => true,
            'isnull' => false,
            'default' => 'Unknown',
            'massupdate' => false,
            'comment' => 'Email direction is one of Unknown, Outbound, Inbound, Internal',
            'reportable' => true,
            'mandatory_fetch' => true,
        ],
        /* relationship collection attributes */
        /* added to support InboundEmail */
        'accounts' => [
            'name' => 'accounts',
            'vname' => 'LBL_EMAILS_ACCOUNTS_REL',
            'type' => 'link',
            'relationship' => 'emails_accounts_rel',
            'module' => 'Accounts',
            'bean_name' => 'Account',
            'source' => 'non-db',
        ],
        'bugs' => [
            'name' => 'bugs',
            'vname' => 'LBL_EMAILS_BUGS_REL',
            'type' => 'link',
            'relationship' => 'emails_bugs_rel',
            'module' => 'Bugs',
            'bean_name' => 'Bug',
            'source' => 'non-db',
        ],
        'cases' => [
            'name' => 'cases',
            'vname' => 'LBL_EMAILS_CASES_REL',
            'type' => 'link',
            'relationship' => 'emails_cases_rel',
            'module' => 'Cases',
            'bean_name' => 'Case',
            'source' => 'non-db',
        ],
        'contacts' => [
            'name' => 'contacts',
            'vname' => 'LBL_EMAILS_CONTACTS_REL',
            'type' => 'link',
            'relationship' => 'emails_contacts_rel',
            'module' => 'Contacts',
            'bean_name' => 'Contact',
            'source' => 'non-db',
        ],
        'escalations' => [
            'name' => 'escalations',
            'vname' => 'LBL_EMAILS_ESCALATIONS_REL',
            'type' => 'link',
            'relationship' => 'emails_escalations_rel',
            'module' => 'Escalations',
            'bean_name' => 'Escalation',
            'source' => 'non-db',
        ],
        'leads' => [
            'name' => 'leads',
            'vname' => 'LBL_EMAILS_LEADS_REL',
            'type' => 'link',
            'relationship' => 'emails_leads_rel',
            'module' => 'Leads',
            'bean_name' => 'Lead',
            'source' => 'non-db',
        ],
        'opportunities' => [
            'name' => 'opportunities',
            'vname' => 'LBL_EMAILS_OPPORTUNITIES_REL',
            'type' => 'link',
            'relationship' => 'emails_opportunities_rel',
            'module' => 'Opportunities',
            'bean_name' => 'Opportunity',
            'source' => 'non-db',
        ],
        'purchases' => [
            'name' => 'purchases',
            'vname' => 'LBL_EMAILS_PURCHASES_REL',
            'type' => 'link',
            'relationship' => 'emails_purchases_rel',
            'module' => 'Purchases',
            'bean_name' => 'Purchase',
            'source' => 'non-db',
        ],
        'purchasedlineitems' => [
            'name' => 'purchasedlineitems',
            'vname' => 'LBL_EMAILS_PURCHASEDLINEITEMS_REL',
            'type' => 'link',
            'relationship' => 'emails_purchasedlineitems_rel',
            'module' => 'Emails',
            'bean_name' => 'Email',
            'source' => 'non-db',
        ],
        'project' => [
            'name' => 'project',
            'vname' => 'LBL_EMAILS_PROJECT_REL',
            'type' => 'link',
            'relationship' => 'emails_projects_rel',
            'module' => 'Project',
            'bean_name' => 'Project',
            'source' => 'non-db',
        ],
        'projecttask' => [
            'name' => 'projecttask',
            'vname' => 'LBL_EMAILS_PROJECT_TASK_REL',
            'type' => 'link',
            'relationship' => 'emails_project_task_rel',
            'module' => 'ProjectTask',
            'bean_name' => 'ProjectTask',
            'source' => 'non-db',
        ],
        'prospects' => [
            'name' => 'prospects',
            'vname' => 'LBL_EMAILS_PROSPECT_REL',
            'type' => 'link',
            'relationship' => 'emails_prospects_rel',
            'module' => 'Prospects',
            'bean_name' => 'Prospect',
            'source' => 'non-db',
        ],
        'quotes' => [
            'name' => 'quotes',
            'vname' => 'LBL_EMAILS_QUOTES_REL',
            'type' => 'link',
            'relationship' => 'emails_quotes',
            'module' => 'Quotes',
            'bean_name' => 'Quote',
            'source' => 'non-db',
        ],
        'revenuelineitems' => [
            'name' => 'revenuelineitems',
            'vname' => 'LBL_EMAILS_REVENUELINEITEMS_REL',
            'type' => 'link',
            'relationship' => 'emails_revenuelineitems_rel',
            'module' => 'RevenueLineItems',
            'bean_name' => 'RevenueLineItem',
            'source' => 'non-db',
            'workflow' => false,
        ],
        'products' => [
            'name' => 'products',
            'vname' => 'LBL_EMAILS_PRODUCTS_REL',
            'type' => 'link',
            'relationship' => 'emails_products_rel',
            'module' => 'Products',
            'bean_name' => 'Product',
            'source' => 'non-db',
        ],
        'tasks' => [
            'name' => 'tasks',
            'vname' => 'LBL_EMAILS_TASKS_REL',
            'type' => 'link',
            'relationship' => 'emails_tasks_rel',
            'module' => 'Tasks',
            'bean_name' => 'Task',
            'source' => 'non-db',
        ],
        'users' => [
            'name' => 'users',
            'vname' => 'LBL_EMAILS_USERS_REL',
            'type' => 'link',
            'relationship' => 'emails_users_rel',
            'module' => 'Users',
            'bean_name' => 'User',
            'source' => 'non-db',
        ],
        'notes' => [
            'name' => 'notes',
            'vname' => 'LBL_EMAILS_NOTES_REL',
            'type' => 'link',
            'relationship' => 'emails_notes_rel',
            'module' => 'Notes',
            'bean_name' => 'Note',
            'source' => 'non-db',
        ],
        'messages' => [
            'name' => 'messages',
            'vname' => 'LBL_EMAILS_MESSAGES_REL',
            'type' => 'link',
            'relationship' => 'emails_messages_rel',
            'module' => 'Messages',
            'bean_name' => 'Message',
            'source' => 'non-db',
        ],
        'attachments' => [
            'bean_name' => 'Note',
            'module' => 'Notes',
            'name' => 'attachments',
            'relationship' => 'emails_attachments',
            'source' => 'non-db',
            'type' => 'link',
            'vname' => 'LBL_ATTACHMENTS',
            'reportable' => false,
            'readonly' => true,
        ],
        'attachments_collection' => [
            'name' => 'attachments_collection',
            'links' => [
                'attachments',
            ],
            'order_by' => 'name:asc',
            'source' => 'non-db',
            'studio' => false,
            'type' => 'collection',
            'vname' => 'LBL_ATTACHMENTS',
            'reportable' => false,
            'hideacl' => true,
        ],
        'total_attachments' => [
            'name' => 'total_attachments',
            'vname' => 'LBL_TOTAL_ATTACHMENTS',
            'type' => 'int',
            'formula' => 'count($attachments)',
            'calculated' => true,
            'enforced' => true,
            'studio' => false,
            'workflow' => false,
            'importable' => false,
            'reportable' => false,
            'massupdate' => false,
            'hideacl' => true,
        ],
        'outbound_email_id' => [
            'name' => 'outbound_email_id',
            'comment' => 'The configuration used to send an email, only used for emails sent using SugarCRM',
            'type' => 'enum',
            'dbType' => 'id',
            'required' => false,
            'vname' => 'LBL_OUTBOUND_EMAIL_ID',
            'function' => 'getOutboundEmailDropdown',
            'function_bean' => 'Emails',
            'reportable' => false,
            'massupdate' => false,
        ],
        'from_collection' => [
            'name' => 'from_collection',
            'links' => [
                'from',
            ],
            'order_by' => 'parent_name:asc',
            'source' => 'non-db',
            'studio' => false,
            'type' => 'collection',
            'vname' => 'LBL_FROM',
            'reportable' => false,
            'hideacl' => true,
            'displayParams' => [
                'fields' => [
                    'email_address_id',
                    'email_address',
                    'parent_type',
                    'parent_id',
                    'parent_name',
                    'invalid_email',
                    'opt_out',
                ],
            ],
        ],
        'to_collection' => [
            'name' => 'to_collection',
            'links' => [
                'to',
            ],
            'order_by' => 'parent_name:asc',
            'source' => 'non-db',
            'studio' => false,
            'type' => 'collection',
            'vname' => 'LBL_TO_ADDRS',
            'reportable' => false,
            'hideacl' => true,
        ],
        'cc_collection' => [
            'name' => 'cc_collection',
            'links' => [
                'cc',
            ],
            'order_by' => 'parent_name:asc',
            'source' => 'non-db',
            'studio' => false,
            'type' => 'collection',
            'vname' => 'LBL_CC',
            'reportable' => false,
            'hideacl' => true,
        ],
        'bcc_collection' => [
            'name' => 'bcc_collection',
            'links' => [
                'bcc',
            ],
            'order_by' => 'parent_name:asc',
            'source' => 'non-db',
            'studio' => false,
            'type' => 'collection',
            'vname' => 'LBL_BCC',
            'reportable' => false,
            'hideacl' => true,
        ],
        'from' => [
            'name' => 'from',
            'relationship' => 'emails_from',
            'source' => 'non-db',
            'type' => 'link',
            'vname' => 'LBL_FROM',
            'reportable' => false,
            'readonly' => true,
        ],
        'to' => [
            'name' => 'to',
            'relationship' => 'emails_to',
            'source' => 'non-db',
            'type' => 'link',
            'vname' => 'LBL_TO_ADDRS',
            'reportable' => false,
            'readonly' => true,
        ],
        'cc' => [
            'name' => 'cc',
            'relationship' => 'emails_cc',
            'source' => 'non-db',
            'type' => 'link',
            'vname' => 'LBL_CC',
            'reportable' => false,
            'readonly' => true,
        ],
        'bcc' => [
            'name' => 'bcc',
            'relationship' => 'emails_bcc',
            'source' => 'non-db',
            'type' => 'link',
            'vname' => 'LBL_BCC',
            'reportable' => false,
            'readonly' => true,
        ],
        'sync_key' => [
            'is_sync_key' => true,
            'name' => 'sync_key',
            'vname' => 'LBL_SYNC_KEY',
            'type' => 'varchar',
            'enforced' => '',
            'required' => false,
            'massupdate' => false,
            'readonly' => true,
            'default' => null,
            'isnull' => true,
            'no_default' => false,
            'comments' => 'External default id of the remote integration record',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'merge_filter' => 'disabled',
            'duplicate_on_record_copy' => 'no',
            'audited' => true,
            'reportable' => true,
            'unified_search' => false,
            'calculated' => false,
            'len' => '100',
            'size' => '20',
            'studio' => [
                'recordview' => true,
                'wirelessdetailview' => true,
                'listview' => false,
                'wirelesseditview' => false,
                'wirelesslistview' => false,
                'wireless_basic_search' => false,
                'wireless_advanced_search' => false,
                'portallistview' => false,
                'portalrecordview' => false,
                'portaleditview' => false,
            ],
        ],
        /* end relationship collections */
    ], /* end fields() array */
    'relationships' => [
        'emails_assigned_user' => [
            'lhs_module' => 'Users',
            'lhs_table' => 'users',
            'lhs_key' => 'id',
            'rhs_module' => 'Emails',
            'rhs_table' => 'emails',
            'rhs_key' => 'assigned_user_id',
            'relationship_type' => 'one-to-many',
        ],
        'emails_modified_user' => [
            'lhs_module' => 'Users',
            'lhs_table' => 'users',
            'lhs_key' => 'id',
            'rhs_module' => 'Emails',
            'rhs_table' => 'emails',
            'rhs_key' => 'modified_user_id',
            'relationship_type' => 'one-to-many',
        ],
        'emails_created_by' => [
            'lhs_module' => 'Users',
            'lhs_table' => 'users',
            'lhs_key' => 'id',
            'rhs_module' => 'Emails',
            'rhs_table' => 'emails',
            'rhs_key' => 'created_by',
            'relationship_type' => 'one-to-many',
        ],
        'emails_attachments' => [
            'lhs_module' => 'Emails',
            'lhs_table' => 'emails',
            'lhs_key' => 'id',
            'rhs_module' => 'Notes',
            'rhs_table' => 'notes',
            'rhs_key' => 'email_id',
            'relationship_type' => 'one-to-many',
            'relationship_class' => 'EmailAttachmentRelationship',
            'relationship_file' => 'modules/Emails/EmailAttachmentRelationship.php',
            'relationship_role_column' => 'email_type',
            'relationship_role_column_value' => 'Emails',
        ],
        'emails_notes_rel' => [
            'lhs_module' => 'Emails',
            'lhs_table' => 'emails',
            'lhs_key' => 'id',
            'rhs_module' => 'Notes',
            'rhs_table' => 'notes',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'emails_beans',
            'join_key_lhs' => 'email_id',
            'join_key_rhs' => 'bean_id',
            'relationship_role_column' => 'bean_module',
            'relationship_role_column_value' => 'Notes',
        ],
        'emails_messages_rel' => [
            'lhs_module' => 'Emails',
            'lhs_table' => 'emails',
            'lhs_key' => 'id',
            'rhs_module' => 'Messages',
            'rhs_table' => 'messages',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'emails_beans',
            'join_key_lhs' => 'email_id',
            'join_key_rhs' => 'bean_id',
            'relationship_role_column' => 'bean_module',
            'relationship_role_column_value' => 'Messages',
        ],
        'emails_revenuelineitems_rel' => [
            'lhs_module' => 'Emails',
            'lhs_table' => 'emails',
            'lhs_key' => 'id',
            'rhs_module' => 'RevenueLineItems',
            'rhs_table' => 'revenue_line_items',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'emails_beans',
            'join_key_lhs' => 'email_id',
            'join_key_rhs' => 'bean_id',
            'relationship_role_column' => 'bean_module',
            'relationship_role_column_value' => 'RevenueLineItems',
        ],
        'emails_purchases_rel' => [
            'lhs_module' => 'Emails',
            'lhs_table' => 'emails',
            'lhs_key' => 'id',
            'rhs_module' => 'Purchases',
            'rhs_table' => 'purchases',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'emails_beans',
            'join_key_lhs' => 'email_id',
            'join_key_rhs' => 'bean_id',
            'relationship_role_column' => 'bean_module',
            'relationship_role_column_value' => 'Purchases',
        ],
        'emails_purchasedlineitems_rel' => [
            'lhs_module' => 'Emails',
            'lhs_table' => 'emails',
            'lhs_key' => 'id',
            'rhs_module' => 'PurchasedLineItems',
            'rhs_table' => 'purchased_line_items',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'emails_beans',
            'join_key_lhs' => 'email_id',
            'join_key_rhs' => 'bean_id',
            'relationship_role_column' => 'bean_module',
            'relationship_role_column_value' => 'PurchasedLineItems',
        ],
        'emails_products_rel' => [
            'lhs_module' => 'Emails',
            'lhs_table' => 'emails',
            'lhs_key' => 'id',
            'rhs_module' => 'Products',
            'rhs_table' => 'products',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'emails_beans',
            'join_key_lhs' => 'email_id',
            'join_key_rhs' => 'bean_id',
            'relationship_role_column' => 'bean_module',
            'relationship_role_column_value' => 'Products',
        ],
        'emails_from' => [
            'lhs_module' => 'Emails',
            'lhs_table' => 'emails',
            'lhs_key' => 'id',
            'rhs_module' => 'EmailParticipants',
            'rhs_table' => 'emails_email_addr_rel',
            'rhs_key' => 'email_id',
            'relationship_type' => 'one-to-one',
            'relationship_class' => 'EmailSenderRelationship',
            'relationship_file' => 'modules/Emails/EmailSenderRelationship.php',
            'relationship_role_columns' => [
                'address_type' => 'from',
            ],
        ],
        'emails_to' => [
            'lhs_module' => 'Emails',
            'lhs_table' => 'emails',
            'lhs_key' => 'id',
            'rhs_module' => 'EmailParticipants',
            'rhs_table' => 'emails_email_addr_rel',
            'rhs_key' => 'email_id',
            'relationship_type' => 'one-to-many',
            'relationship_class' => 'EmailRecipientRelationship',
            'relationship_file' => 'modules/Emails/EmailRecipientRelationship.php',
            'relationship_role_columns' => [
                'address_type' => 'to',
            ],
        ],
        'emails_cc' => [
            'lhs_module' => 'Emails',
            'lhs_table' => 'emails',
            'lhs_key' => 'id',
            'rhs_module' => 'EmailParticipants',
            'rhs_table' => 'emails_email_addr_rel',
            'rhs_key' => 'email_id',
            'relationship_type' => 'one-to-many',
            'relationship_class' => 'EmailRecipientRelationship',
            'relationship_file' => 'modules/Emails/EmailRecipientRelationship.php',
            'relationship_role_columns' => [
                'address_type' => 'cc',
            ],
        ],
        'emails_bcc' => [
            'lhs_module' => 'Emails',
            'lhs_table' => 'emails',
            'lhs_key' => 'id',
            'rhs_module' => 'EmailParticipants',
            'rhs_table' => 'emails_email_addr_rel',
            'rhs_key' => 'email_id',
            'relationship_type' => 'one-to-many',
            'relationship_class' => 'EmailRecipientRelationship',
            'relationship_file' => 'modules/Emails/EmailRecipientRelationship.php',
            'relationship_role_columns' => [
                'address_type' => 'bcc',
            ],
        ],
    ], // end relationships
    'indices' => [
        [
            'name' => 'emailspk',
            'type' => 'primary',
            'fields' => ['id'],
        ],
        [
            'name' => 'idx_email_name',
            'type' => 'index',
            'fields' => ['name'],
        ],
        [
            'name' => 'idx_message_id',
            'type' => 'index',
            'fields' => ['message_id'],
        ],
        [
            'name' => 'idx_email_parent_id',
            'type' => 'index',
            'fields' => ['parent_id'],
        ],
        [
            'name' => 'idx_email_assigned',
            'type' => 'index',
            'fields' => ['assigned_user_id', 'type', 'status'],
        ],
        [
            'name' => 'idx_date_modified',
            'type' => 'index',
            'fields' => ['date_modified'],
        ],
        [
            'name' => 'idx_state',
            'type' => 'index',
            'fields' => ['state', 'id'],
        ],
        [
            'name' => 'idx_mailbox_id',
            'type' => 'index',
            'fields' => ['mailbox_id'],
        ],
        [
            'name' => 'idx_emails_skey',
            'type' => 'unique',
            'fields' => ['sync_key'],
        ],
    ], // end indices
    'uses' => [
        'favorite',
        'following',
        'taggable',
    ],
    'processes' => [
        // Forcefully enable this module even if it is marked as invalid by the engine
        'enabled' => true,
        // If types is left off, that means this module supports all actions with all of its fields
        // If the types property is supplied, it will give explicit instructions to the engine by type
        'types' => [
            // Change Field actions should expose teams and assigned user
            'CF' => [
                'teams',
                'assigned_user_id',
            ],
            // Business Rules should expose assigned user for conclusions
            'BR' => [
                'assigned_user_id',
            ],
            // No fields are supported for Add Related Record action
            'AC' => [],
            // No fields are supported for Process Definition (designer canvas) action
            'RR' => [],
            // No fields are supported for Requird Fields in Activity action
            'RQF' => [],
            // No fields are supported for Readonly Fields in Activity action
            'ROF' => [],
            // Exposed fields for Process Definitions
            'PD' => [
                'created_by_name',
                'date_entered',
                'date_modified',
                'date_sent',
                'direction',
                'modified_by_name',
                'reply_to_status',
                'state',
                'name',
            ],
            // Exposed fields for Business Rules conditions
            'BRR' => [
                'created_by_name',
                'date_entered',
                'date_modified',
                'date_sent',
                'direction',
                'modified_by_name',
                'reply_to_status',
                'state',
                'name',
            ],
            // Exposed fields for Email Templates
            'ET' => [
                'created_by_name',
                'date_entered',
                'date_modified',
                'date_sent',
                'direction',
                'modified_by_name',
                'reply_to_status',
                'state',
                'name',
            ],
        ],
    ],
    'portal_visibility' => [
        'class' => 'Emails',
    ],
];

VardefManager::createVardef(
    'Emails',
    'Email',
    ['team_security']
);

$dictionary['Email']['visibility']['EmailsVisibility'] = true;
