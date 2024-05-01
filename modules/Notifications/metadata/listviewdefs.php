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


$module_name = 'Notifications';
$listViewDefs[$module_name] = [
    'NAME' => [
        'width' => '32',
        'label' => 'LBL_NAME',
        'default' => true,
        'link' => true],
    'TEAM_NAME' => [
        'width' => '9',
        'label' => 'LBL_TEAM',
        'default' => false],
    'ASSIGNED_USER_NAME' => [
        'width' => '9',
        'label' => 'LBL_ASSIGNED_TO_NAME',
        'default' => true],

];
