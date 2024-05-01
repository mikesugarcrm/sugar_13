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

$viewdefs['Bugs']['listview'] = [
    'BUG_NUMBER' => [
        'width' => '5',
        //'label' => 'LBL_LIST_NUMBER',
        'label' => 'LBL_BUG_NUMBER',
        'link' => true,
        'default' => true,
    ],
    'NAME' => [
        'width' => '30',
        'label' => 'LBL_LIST_SUBJECT',
        'default' => true,
        'link' => true,
    ],
    'STATUS' => [
        'width' => '10',
        'label' => 'LBL_LIST_STATUS',
        'default' => true,
    ],
    'TYPE' => [
        'width' => '10',
        'label' => 'LBL_LIST_TYPE',
        'default' => true,
    ],
    'PRIORITY' => [
        'width' => '10',
        'label' => 'LBL_LIST_PRIORITY',
        'default' => false,
    ],
    /*
        'RELEASE_NAME' => array(
            'width' => '10',
            'label' => 'LBL_FOUND_IN_RELEASE',
            'default' => false,
            'related_fields' => array('found_in_release'),
            'module' => 'Releases',
            'id' => 'FOUND_IN_RELEASE',),
        'FIXED_IN_RELEASE_NAME' => array(
            'width' => '15',
            'label' => 'LBL_LIST_FIXED_IN_RELEASE',
            'default' => true,
            'related_fields' => array('fixed_in_release'),
            'module' => 'Releases',
            'id' => 'FIXED_IN_RELEASE',),
    */
    'RESOLUTION' => [
        'width' => '10',
        'label' => 'LBL_LIST_RESOLUTION',
        'default' => false,
    ],
];
