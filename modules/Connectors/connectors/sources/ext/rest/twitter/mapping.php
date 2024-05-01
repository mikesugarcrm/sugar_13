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

/*********************************************************************************
 * Description:
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc. All Rights
 * Reserved. Contributor(s): contact@synolia.com - www.synolia.com
 * *******************************************************************************/

$mapping = [
    'beans' => [
        'Accounts' => [
            'name' => 'name',
            'id' => 'id',
        ],
        'Contacts' => [
            'name' => 'full_name',
            'id' => 'id',
        ],
        'Leads' => [
            'name' => 'account_name',
            'id' => 'id',
        ],
        'Prospects' => [
            'name' => 'account_name',
            'id' => 'id',
        ],
    ],
];
