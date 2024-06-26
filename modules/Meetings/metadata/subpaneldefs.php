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

$layout_defs['Meetings'] = [
    // list of what Subpanels to show in the DetailView
    'subpanel_setup' => [
        'contacts' => [
            'top_buttons' => [],
            'order' => 10,
            'module' => 'Contacts',
            'sort_order' => 'asc',
            'sort_by' => 'last_name, first_name',
            'subpanel_name' => 'ForMeetings',
            'get_subpanel_data' => 'contacts',
            'title_key' => 'LBL_CONTACTS_SUBPANEL_TITLE',
        ],
        'users' => [
            'top_buttons' => [],
            'order' => 20,
            'module' => 'Users',
            'sort_order' => 'asc',
            'sort_by' => 'name',
            'subpanel_name' => 'ForMeetings',
            'get_subpanel_data' => 'users',
            'title_key' => 'LBL_USERS_SUBPANEL_TITLE',
        ],
        'leads' => [
            'order' => 30,
            'module' => 'Leads',
            'sort_order' => 'asc',
            'sort_by' => 'last_name, first_name',
            'subpanel_name' => 'ForMeetings',
            'get_subpanel_data' => 'leads',
            'title_key' => 'LBL_LEADS_SUBPANEL_TITLE',
            'top_buttons' => [],
        ],
        'history' => [
            'order' => 40,
            'sort_order' => 'desc',
            'sort_by' => 'date_entered',
            'title_key' => 'LBL_HISTORY_SUBPANEL_TITLE',
            'type' => 'collection',
            'subpanel_name' => 'history',   //this values is not associated with a physical file.
            'header_definition_from_subpanel' => 'meetings',
            'module' => 'History',

            'top_buttons' => [
                ['widget_class' => 'SubPanelTopCreateNoteButton'],
            ],

            'collection_list' => [
                'notes' => [
                    'module' => 'Notes',
                    'subpanel_name' => 'ForMeetings',
                    'get_subpanel_data' => 'notes',
                ],
            ],
        ],
    ],
];
