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
$searchdefs ['Notes'] =
    [
        'layout' => [
            'basic_search' => [
                0 => 'name',
                1 => [
                    'name' => 'contact_name',
                    'label' => 'LBL_CONTACT_NAME',
                    'type' => 'name',
                ],
            ],
            'advanced_search' => [
                'name' => [
                    'name' => 'name',
                    'label' => 'LBL_NOTE_SUBJECT',
                    'default' => true,
                ],
                'filename' => [
                    'name' => 'filename',
                    'label' => 'LBL_FILENAME',
                    'default' => true,
                ],
                'date_entered' => [
                    'width' => '10%',
                    'label' => 'LBL_DATE_ENTERED',
                    'default' => true,
                    'name' => 'date_entered',
                ],
                'date_modified' => [
                    'width' => '10%',
                    'label' => 'LBL_DATE_MODIFIED',
                    'default' => true,
                    'name' => 'date_modified',
                ],
                'portal_flag' => [
                    'width' => '10%',
                    'label' => 'LBL_PORTAL_FLAG',
                    'default' => true,
                    'name' => 'portal_flag',
                ],
                'embed_flag' => [
                    'width' => '10%',
                    'label' => 'LBL_EMBED_FLAG',
                    'default' => true,
                    'name' => 'embed_flag',
                ],
                'parent_name' => [
                    'width' => '10%',
                    'label' => 'LBL_RELATED_TO',
                    'default' => true,
                    'name' => 'parent_name',
                ],
            ],
        ],
        'templateMeta' => [
            'maxColumns' => '3',
            'widths' => [
                'label' => '10',
                'field' => '30',
            ],
        ],
    ];
