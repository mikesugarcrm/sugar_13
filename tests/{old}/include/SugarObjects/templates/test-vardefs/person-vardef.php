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
$vardefs['fields']['customField'] = [
    'name' => 'customField',
    'type' => 'varchar',
    'len' => '100',
    'unified_search' => true,
    'duplicate_on_record_copy' => 'always',
    'full_text_search' => ['enabled' => true, 'boost' => 3],
    'comment' => 'customTestField',
    'merge_filter' => 'selected',
];
