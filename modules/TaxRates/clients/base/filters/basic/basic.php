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
$viewdefs['TaxRates']['base']['filter']['basic'] = [
    'create' => false,
    'quicksearch_field' => ['name'],
    'quicksearch_priority' => 1,
    'filters' => [
        [
            'id' => 'all_records', // need 'all_records' to make filter irremovable
            'name' => 'LBL_MODULE_NAME',
            'filter_definition' => [],
            'editable' => false,
        ],
        [
            'id' => 'active_taxrates',
            'name' => 'LBL_FILTER_ACTIVE',
            'filter_definition' => [
                'status' => [
                    '$in' => ['Active'],
                ],
            ],
            'editable' => false,
        ],
    ],
];
