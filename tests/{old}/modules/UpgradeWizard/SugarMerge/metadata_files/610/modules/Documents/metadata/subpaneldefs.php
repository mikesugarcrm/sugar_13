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


$layout_defs['Documents'] = [
    // list of what Subpanels to show in the DetailView
    'subpanel_setup' => [
        'therevisions' => [
            'order' => 10,
            'sort_order' => 'desc',
            'sort_by' => 'revision',
            'module' => 'DocumentRevisions',
            'subpanel_name' => 'default',
            'title_key' => 'LBL_DOC_REV_HEADER',
            'get_subpanel_data' => 'revisions',
            'fill_in_additional_fields' => true,
        ],
        'contracts' => [
            'order' => 20,
            'sort_order' => 'desc',
            'sort_by' => 'name',
            'module' => 'Contracts',
            'subpanel_name' => 'ForDocuments',
            'get_subpanel_data' => 'contracts',
            'add_subpanel_data' => 'contract_id',
            'title_key' => 'LBL_CONTRACTS_SUBPANEL_TITLE',
            'top_buttons' => [],
        ],
    ],
];
