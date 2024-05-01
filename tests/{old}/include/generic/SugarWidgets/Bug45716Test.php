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

class Bug45716Helper
{
    public $all_fields = [
        'self:account_id' => [
            'name' => 'account_id',
            'vname' => 'LBL_ACCOUNT_ID',
            'type' => 'id',
            'source' => 'non-db',
            'audited' => true,
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:account_id_c' => [
            'required' => false,
            'source' => 'custom_fields',
            'name' => 'account_id_c',
            'vname' => 'LBL_LIST_RELATED_TO',
            'type' => 'id',
            'massupdate' => '0',
            'default' => null,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'calculated' => false,
            'len' => '36',
            'size' => '20',
            'id' => 'Opportunitiesaccount_id_c',
            'custom_module' => 'Opportunities',
            'module' => 'Opportunities',
            'real_table' => 'opportunities_cstm',
        ],
        'self:account_link_c' => [
            'dependency' => '',
            'required' => false,
            'source' => 'non-db',
            'name' => 'account_link_c',
            'vname' => 'LBL_ACCOUNT_LINK',
            'type' => 'relate',
            'massupdate' => '0',
            'default' => null,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => true,
            'unified_search' => false,
            'calculated' => false,
            'len' => '255',
            'size' => '20',
            'id_name' => 'account_id_c',
            'ext2' => 'Accounts',
            'module' => 'Opportunities',
            'rname' => 'name',
            'quicksearch' => 'enabled',
            'studio' => 'visible',
            'id' => 'Opportunitiesaccount_link_c',
            'custom_module' => 'Opportunities',
            'real_table' => 'opportunities_cstm',
            'secondary_table' => 'accounts',
        ],
        'self:account_name' => [
            'name' => 'account_name',
            'rname' => 'name',
            'id_name' => 'account_id',
            'vname' => 'LBL_ACCOUNT_NAME',
            'type' => 'relate',
            'table' => 'accounts',
            'join_name' => 'accounts',
            'isnull' => 'true',
            'module' => 'Opportunities',
            'dbType' => 'varchar',
            'link' => 'accounts',
            'len' => '255',
            'source' => 'non-db',
            'unified_search' => true,
            'required' => true,
            'importable' => 'required',
            'real_table' => 'opportunities',
        ],
        'self:accounts' => [
            'name' => 'accounts',
            'type' => 'link',
            'relationship' => 'accounts_opportunities',
            'source' => 'non-db',
            'link_type' => 'one',
            'module' => 'Opportunities',
            'bean_name' => 'Account',
            'vname' => 'LBL_ACCOUNTS',
            'real_table' => 'opportunities',
        ],
        'self:amount' => [
            'name' => 'amount',
            'vname' => 'LBL_AMOUNT',
            'type' => 'currency',
            'dbType' => 'double',
            'comment' => 'Unconverted amount of the opportunity',
            'importable' => 'required',
            'duplicate_merge' => '1',
            'required' => true,
            'options' => 'numeric_range_search_dom',
            'enable_range_search' => true,
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:amount_usdollar' => [
            'name' => 'amount_usdollar',
            'vname' => 'LBL_AMOUNT_USDOLLAR',
            'type' => 'currency',
            'group' => 'amount',
            'dbType' => 'double',
            'disable_num_format' => true,
            'duplicate_merge' => '0',
            'audited' => true,
            'comment' => 'Formatted amount of the opportunity',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:assigned_user_id' => [
            'name' => 'assigned_user_id',
            'rname' => 'user_name',
            'id_name' => 'assigned_user_id',
            'vname' => 'LBL_ASSIGNED_TO_ID',
            'group' => 'assigned_user_name',
            'type' => 'relate',
            'table' => 'users',
            'module' => 'Opportunities',
            'reportable' => true,
            'isnull' => 'false',
            'dbType' => 'id',
            'audited' => true,
            'comment' => 'User ID assigned to record',
            'duplicate_merge' => 'disabled',
            'real_table' => 'opportunities',
        ],
        'self:assigned_user_link' => [
            'name' => 'assigned_user_link',
            'type' => 'link',
            'relationship' => 'opportunities_assigned_user',
            'vname' => 'LBL_ASSIGNED_TO_USER',
            'link_type' => 'one',
            'module' => 'Opportunities',
            'bean_name' => 'User',
            'source' => 'non-db',
            'duplicate_merge' => 'enabled',
            'rname' => 'user_name',
            'id_name' => 'assigned_user_id',
            'table' => 'users',
            'real_table' => 'opportunities',
        ],
        'self:assigned_user_name' => [
            'name' => 'assigned_user_name',
            'link' => 'assigned_user_link',
            'vname' => 'LBL_ASSIGNED_TO_NAME',
            'rname' => 'user_name',
            'type' => 'relate',
            'reportable' => false,
            'source' => 'non-db',
            'table' => 'users',
            'id_name' => 'assigned_user_id',
            'module' => 'Opportunities',
            'duplicate_merge' => 'disabled',
            'real_table' => 'opportunities',
        ],
        'self:calls' => [
            'name' => 'calls',
            'type' => 'link',
            'relationship' => 'opportunity_calls',
            'source' => 'non-db',
            'vname' => 'LBL_CALLS',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:campaign_id' => [
            'name' => 'campaign_id',
            'comment' => 'Campaign that generated lead',
            'vname' => 'LBL_CAMPAIGN_ID',
            'rname' => 'id',
            'type' => 'id',
            'dbType' => 'id',
            'table' => 'campaigns',
            'isnull' => 'true',
            'module' => 'Opportunities',
            'reportable' => false,
            'massupdate' => false,
            'duplicate_merge' => 'disabled',
            'real_table' => 'opportunities',
        ],
        'self:campaign_name' => [
            'name' => 'campaign_name',
            'rname' => 'name',
            'id_name' => 'campaign_id',
            'vname' => 'LBL_CAMPAIGN',
            'type' => 'relate',
            'link' => 'campaign_opportunities',
            'isnull' => 'true',
            'table' => 'campaigns',
            'module' => 'Opportunities',
            'source' => 'non-db',
            'real_table' => 'opportunities',
        ],
        'self:campaign_opportunities' => [
            'name' => 'campaign_opportunities',
            'type' => 'link',
            'vname' => 'LBL_CAMPAIGN_OPPORTUNITY',
            'relationship' => 'campaign_opportunities',
            'source' => 'non-db',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:campaigns' => [
            'name' => 'campaigns',
            'type' => 'link',
            'relationship' => 'campaign_opportunities',
            'module' => 'Opportunities',
            'bean_name' => 'CampaignLog',
            'source' => 'non-db',
            'vname' => 'LBL_CAMPAIGNS',
            'reportable' => false,
            'real_table' => 'opportunities',
        ],
        'self:contacts' => [
            'name' => 'contacts',
            'type' => 'link',
            'relationship' => 'opportunities_contacts',
            'source' => 'non-db',
            'module' => 'Opportunities',
            'bean_name' => 'Contact',
            'rel_fields' => [
                'contact_role' => [
                    'type' => 'enum',
                    'options' => 'opportunity_relationship_type_dom',
                ],
            ],
            'vname' => 'LBL_CONTACTS',
            'real_table' => 'opportunities',
        ],
        'self:contracts' => [
            'name' => 'contracts',
            'type' => 'link',
            'vname' => 'LBL_CONTRACTS',
            'relationship' => 'contracts_opportunities',
            'source' => 'non-db',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:created_by' => [
            'name' => 'created_by',
            'rname' => 'user_name',
            'id_name' => 'modified_user_id',
            'vname' => 'LBL_CREATED',
            'type' => 'assigned_user_name',
            'table' => 'users',
            'isnull' => 'false',
            'dbType' => 'id',
            'group' => 'created_by_name',
            'comment' => 'User who created record',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:created_by_link' => [
            'name' => 'created_by_link',
            'type' => 'link',
            'relationship' => 'opportunities_created_by',
            'vname' => 'LBL_CREATED_USER',
            'link_type' => 'one',
            'module' => 'Opportunities',
            'bean_name' => 'User',
            'source' => 'non-db',
            'real_table' => 'opportunities',
        ],
        'self:created_by_name' => [
            'name' => 'created_by_name',
            'vname' => 'LBL_CREATED',
            'type' => 'relate',
            'reportable' => false,
            'link' => 'created_by_link',
            'rname' => 'user_name',
            'source' => 'non-db',
            'table' => 'users',
            'id_name' => 'created_by',
            'module' => 'Opportunities',
            'duplicate_merge' => 'disabled',
            'importable' => 'false',
            'real_table' => 'opportunities',
        ],
        'self:currencies' => [
            'name' => 'currencies',
            'type' => 'link',
            'relationship' => 'opportunity_currencies',
            'source' => 'non-db',
            'vname' => 'LBL_CURRENCIES',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:currency_id' => [
            'name' => 'currency_id',
            'type' => 'id',
            'group' => 'currency_id',
            'vname' => 'LBL_CURRENCY',
            'function' => 'getCurrencies',
            'function_bean' => 'Currencies',
            'reportable' => false,
            'comment' => 'Currency used for display purposes',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:currency_name' => [
            'name' => 'currency_name',
            'rname' => 'name',
            'id_name' => 'currency_id',
            'vname' => 'LBL_CURRENCY_NAME',
            'type' => 'relate',
            'isnull' => 'true',
            'table' => 'currencies',
            'module' => 'Opportunities',
            'source' => 'non-db',
            'function' => 'getCurrencies',
            'function_bean' => 'Currencies',
            'studio' => 'false',
            'duplicate_merge' => 'disabled',
            'real_table' => 'opportunities',
        ],
        'self:currency_symbol' => [
            'name' => 'currency_symbol',
            'rname' => 'symbol',
            'id_name' => 'currency_id',
            'vname' => 'LBL_CURRENCY_SYMBOL',
            'type' => 'relate',
            'isnull' => 'true',
            'table' => 'currencies',
            'module' => 'Opportunities',
            'source' => 'non-db',
            'function' => 'getCurrencySymbols',
            'function_bean' => 'Currencies',
            'studio' => 'false',
            'duplicate_merge' => 'disabled',
            'real_table' => 'opportunities',
        ],
        'self:currency_target_c' => [
            'required' => false,
            'source' => 'custom_fields',
            'name' => 'currency_target_c',
            'vname' => 'LBL_CURRENCY_TARGET',
            'type' => 'currency',
            'massupdate' => '0',
            'default' => null,
            'comments' => '',
            'help' => '',
            'importable' => 'false',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => true,
            'unified_search' => false,
            'calculated' => false,
            'len' => '26',
            'size' => '20',
            'enable_range_search' => false,
            'id' => 'Opportunitiescurrency_target_c',
            'custom_module' => 'Opportunities',
            'module' => 'Opportunities',
            'real_table' => 'opportunities_cstm',
        ],
        'self:date_closed' => [
            'name' => 'date_closed',
            'vname' => 'LBL_DATE_CLOSED',
            'type' => 'date',
            'audited' => true,
            'comment' => 'Expected or actual date the oppportunity will close',
            'importable' => 'required',
            'required' => true,
            'enable_range_search' => true,
            'options' => 'date_range_search_dom',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:date_entered' => [
            'name' => 'date_entered',
            'vname' => 'LBL_DATE_ENTERED',
            'type' => 'datetime',
            'group' => 'created_by_name',
            'comment' => 'Date record created',
            'enable_range_search' => true,
            'options' => 'date_range_search_dom',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:date_modified' => [
            'name' => 'date_modified',
            'vname' => 'LBL_DATE_MODIFIED',
            'type' => 'datetime',
            'group' => 'modified_by_name',
            'comment' => 'Date record last modified',
            'enable_range_search' => true,
            'options' => 'date_range_search_dom',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:deleted' => [
            'name' => 'deleted',
            'vname' => 'LBL_DELETED',
            'type' => 'bool',
            'default' => '0',
            'reportable' => false,
            'comment' => 'Record deletion indicator',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:description' => [
            'name' => 'description',
            'vname' => 'LBL_DESCRIPTION',
            'type' => 'text',
            'comment' => 'Full text of the note',
            'rows' => 6,
            'cols' => 80,
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:documents' => [
            'name' => 'documents',
            'type' => 'link',
            'relationship' => 'documents_opportunities',
            'source' => 'non-db',
            'vname' => 'LBL_DOCUMENTS_SUBPANEL_TITLE',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:emails' => [
            'name' => 'emails',
            'type' => 'link',
            'relationship' => 'emails_opportunities_rel',
            'source' => 'non-db',
            'vname' => 'LBL_EMAILS',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:id' => [
            'name' => 'id',
            'vname' => 'LBL_ID',
            'type' => 'id',
            'required' => true,
            'reportable' => true,
            'comment' => 'Unique identifier',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:lead_source' => [
            'name' => 'lead_source',
            'vname' => 'LBL_LEAD_SOURCE',
            'type' => 'enum',
            'options' => 'lead_source_dom',
            'len' => '50',
            'comment' => 'Source of the opportunity',
            'merge_filter' => 'enabled',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:leads' => [
            'name' => 'leads',
            'type' => 'link',
            'relationship' => 'opportunity_leads',
            'source' => 'non-db',
            'vname' => 'LBL_LEADS',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:meetings' => [
            'name' => 'meetings',
            'type' => 'link',
            'relationship' => 'opportunity_meetings',
            'source' => 'non-db',
            'vname' => 'LBL_MEETINGS',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:modified_by_name' => [
            'name' => 'modified_by_name',
            'vname' => 'LBL_MODIFIED_NAME',
            'type' => 'relate',
            'reportable' => false,
            'source' => 'non-db',
            'rname' => 'user_name',
            'table' => 'users',
            'id_name' => 'modified_user_id',
            'module' => 'Opportunities',
            'link' => 'modified_user_link',
            'duplicate_merge' => 'disabled',
            'real_table' => 'opportunities',
        ],
        'self:modified_user_id' => [
            'name' => 'modified_user_id',
            'rname' => 'user_name',
            'id_name' => 'modified_user_id',
            'vname' => 'LBL_MODIFIED',
            'type' => 'assigned_user_name',
            'table' => 'users',
            'isnull' => 'false',
            'group' => 'modified_by_name',
            'dbType' => 'id',
            'reportable' => true,
            'comment' => 'User who last modified record',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:modified_user_link' => [
            'name' => 'modified_user_link',
            'type' => 'link',
            'relationship' => 'opportunities_modified_user',
            'vname' => 'LBL_MODIFIED_USER',
            'link_type' => 'one',
            'module' => 'Opportunities',
            'bean_name' => 'User',
            'source' => 'non-db',
            'real_table' => 'opportunities',
        ],
        'self:name' => [
            'name' => 'name',
            'vname' => 'LBL_OPPORTUNITY_NAME',
            'type' => 'name',
            'dbType' => 'varchar',
            'len' => '50',
            'unified_search' => true,
            'comment' => 'Name of the opportunity',
            'merge_filter' => 'selected',
            'importable' => 'required',
            'required' => true,
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:next_step' => [
            'name' => 'next_step',
            'vname' => 'LBL_NEXT_STEP',
            'type' => 'varchar',
            'len' => '100',
            'comment' => 'The next step in the sales process',
            'merge_filter' => 'enabled',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:notes' => [
            'name' => 'notes',
            'type' => 'link',
            'relationship' => 'opportunity_notes',
            'source' => 'non-db',
            'vname' => 'LBL_NOTES',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:opportunity_type' => [
            'name' => 'opportunity_type',
            'vname' => 'LBL_TYPE',
            'type' => 'enum',
            'options' => 'opportunity_type_dom',
            'len' => '255',
            'audited' => true,
            'comment' => 'Type of opportunity (ex: Existing, New)',
            'merge_filter' => 'enabled',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:probability' => [
            'name' => 'probability',
            'vname' => 'LBL_PROBABILITY',
            'type' => 'int',
            'dbType' => 'double',
            'audited' => true,
            'comment' => 'The probability of closure',
            'validation' => [
                'type' => 'range',
                'min' => 0,
                'max' => 100,
            ],
            'merge_filter' => 'enabled',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:project' => [
            'name' => 'project',
            'type' => 'link',
            'relationship' => 'projects_opportunities',
            'source' => 'non-db',
            'vname' => 'LBL_PROJECTS',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:quotes' => [
            'name' => 'quotes',
            'type' => 'link',
            'relationship' => 'quotes_opportunities',
            'source' => 'non-db',
            'vname' => 'LBL_QUOTES',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:sales_stage' => [
            'name' => 'sales_stage',
            'vname' => 'LBL_SALES_STAGE',
            'type' => 'enum',
            'options' => 'sales_stage_dom',
            'len' => '255',
            'audited' => true,
            'comment' => 'Indication of progression towards closure',
            'merge_filter' => 'enabled',
            'importable' => 'required',
            'required' => true,
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:tasks' => [
            'name' => 'tasks',
            'type' => 'link',
            'relationship' => 'opportunity_tasks',
            'source' => 'non-db',
            'vname' => 'LBL_TASKS',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:team_count' => [
            'name' => 'team_count',
            'rname' => 'team_count',
            'id_name' => 'team_id',
            'vname' => 'LBL_TEAMS',
            'join_name' => 'ts1',
            'table' => 'teams',
            'type' => 'relate',
            'required' => 'true',
            'isnull' => 'true',
            'module' => 'Opportunities',
            'link' => 'team_count_link',
            'massupdate' => false,
            'dbType' => 'int',
            'source' => 'non-db',
            'importable' => 'false',
            'reportable' => false,
            'duplicate_merge' => 'disabled',
            'studio' => 'false',
            'hideacl' => true,
            'real_table' => 'opportunities',
        ],
        'self:team_count_link' => [
            'name' => 'team_count_link',
            'type' => 'link',
            'relationship' => 'opportunities_team_count_relationship',
            'link_type' => 'one',
            'module' => 'Opportunities',
            'bean_name' => 'TeamSet',
            'source' => 'non-db',
            'duplicate_merge' => 'disabled',
            'reportable' => false,
            'studio' => 'false',
            'real_table' => 'opportunities',
        ],
        'self:team_id' => [
            'name' => 'team_id',
            'vname' => 'LBL_TEAM_ID',
            'group' => 'team_name',
            'reportable' => false,
            'dbType' => 'id',
            'type' => 'team_list',
            'audited' => true,
            'comment' => 'Team ID for the account',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:team_link' => [
            'name' => 'team_link',
            'type' => 'link',
            'relationship' => 'opportunities_team',
            'vname' => 'LBL_TEAMS_LINK',
            'link_type' => 'one',
            'module' => 'Opportunities',
            'bean_name' => 'Team',
            'source' => 'non-db',
            'duplicate_merge' => 'disabled',
            'studio' => 'false',
            'real_table' => 'opportunities',
        ],
        'self:team_name' => [
            'name' => 'team_name',
            'db_concat_fields' => [
                0 => 'name',
                1 => 'name_2',
            ],
            'sort_on' => 'tj.name',
            'join_name' => 'tj',
            'rname' => 'name',
            'id_name' => 'team_id',
            'vname' => 'LBL_TEAMS',
            'type' => 'relate',
            'required' => 'true',
            'table' => 'teams',
            'isnull' => 'true',
            'module' => 'Opportunities',
            'link' => 'team_link',
            'massupdate' => false,
            'dbType' => 'varchar',
            'source' => 'non-db',
            'len' => 36,
            'custom_type' => 'teamset',
            'real_table' => 'opportunities',
        ],
        'self:team_set_id' => [
            'name' => 'team_set_id',
            'rname' => 'id',
            'id_name' => 'team_set_id',
            'vname' => 'LBL_TEAM_SET_ID',
            'type' => 'team_set_id',
            'audited' => true,
            'studio' => 'false',
            'dbType' => 'id',
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
        'self:teams' => [
            'name' => 'teams',
            'type' => 'link',
            'relationship' => 'opportunities_teams',
            'bean_filter_field' => 'team_set_id',
            'rhs_key_override' => true,
            'source' => 'non-db',
            'vname' => 'LBL_TEAMS',
            'link_class' => 'TeamSetLink',
            'studio' => 'false',
            'reportable' => false,
            'module' => 'Opportunities',
            'real_table' => 'opportunities',
        ],
    ]; // END: all_fields


    public $selected_loaded_custom_links = [
        'opportunities_cstm' => [
            'join_table_alias' => 'opportunities_cstm',
            'base_table' => 'opportunities',
            'real_table' => 'opportunities_cstm',
        ],
        'accounts_account_link_c' => [
            'join_table_alias' => 'accounts1',
            'base_table' => 'accounts',
            'join_id' => 'opportunities_cstm.account_id_c',
        ],
    ]; // END: selected_loaded_custom_links

    public $embeddedData = false;
    public function getAttribute()
    {
        return $this;
    }
}

class Bug45716Test extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['action'] = 'index';
        $GLOBALS['module'] = 'Reports';
        $GLOBALS['app_strings'] = return_application_language('en_us');
        $GLOBALS['app_list_strings'] = return_app_list_strings_language('en_us');
        $GLOBALS['mod_strings'] = return_module_language('en_us', 'Reports');
        $GLOBALS['db'] = DBManagerFactory::getInstance();
        $GLOBALS['current_user'] = new User();
        $GLOBALS['current_user']->retrieve('1');
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['module']);
        unset($GLOBALS['action']);
        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['current_user']);
    }

    public function testCustomRelatedLink()
    {
        $layout_def = [
            'name' => 'account_id_c',
            'label' => 'Account Link',
            'table_key' => 'self',
            'table_alias' => 'opportunities_cstm',
            'column_key' => 'self:account_link_c',
            'type' => 'relate',
            'fields' => [
                'PRIMARYID' => '10765534-ff52-52ec-5840-4f16faec901f',
                'OPPORTUNITIES_NAME' => 'Trait Institute Inc - 1000 units',
                'OPPORTUNITIES_AMOUNT_UBC8F31' => '52183382.29',
                'OPPORTUNITIES_AMOUNT' => '52183382.29',
                'OPPORTUNITIES_AMOUNT_C9AC638' => '-99',
                'OPPORTUNITIES_CSTM_ACCE36316' => '13ce632e-605e-93ac-c209-4f16fa14e616',
                'ACCOUNTS1_NAME' => 'OTC Holdings',
            ],
        ];
        $fakeLayoutManager = new Bug45716Helper();
        $sugarWidget = new SugarWidgetFieldRelate($fakeLayoutManager);

        $output = $sugarWidget->displayList($layout_def);

        $this->assertStringContainsString('13ce', $output);
    }
}