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

$viewdefs['<module_name>']['base']['view']['activity-card-definition-for-audit'] = [
    'module' => 'Audit',
    'record_date' => 'date_created',
    'fields' => [
        'assigned_user_id',
        'name',
        'sales_stage',
        'amount_usdollar',
    ],
];
