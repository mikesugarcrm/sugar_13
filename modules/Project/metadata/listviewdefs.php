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


$listViewDefs['Project'] = [
    'NAME' => [
        'width' => '40',
        'label' => 'LBL_LIST_NAME',
        'link' => true,
        'default' => true],
    'ESTIMATED_START_DATE' => [
        'width' => '20',
        'label' => 'LBL_DATE_START',
        'link' => false,
        'default' => true],
    'ESTIMATED_END_DATE' => [
        'width' => '20',
        'label' => 'LBL_DATE_END',
        'link' => false,
        'default' => true],
    'STATUS' => [
        'width' => '20',
        'label' => 'LBL_STATUS',
        'link' => false,
        'default' => true],
    'ASSIGNED_USER_NAME' => [
        'width' => '10',
        'label' => 'LBL_LIST_ASSIGNED_USER_ID',
        'module' => 'Employees',
        'id' => 'ASSIGNED_USER_ID',
        'default' => true],
    'TEAM_NAME' => [
        'width' => '2',
        'label' => 'LBL_LIST_TEAM',
        'related_fields' => ['team_id'],
        'default' => false],

];
