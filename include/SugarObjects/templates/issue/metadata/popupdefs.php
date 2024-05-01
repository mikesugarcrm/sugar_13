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
$module_name = '<module_name>';
$object_name = '<object_name>';
$_module_name = '<_module_name>';
$_object_name = '<_object_name>';
$popupMeta = ['moduleMain' => $module_name,
    'varName' => $object_name,
    'orderBy' => $_module_name . '.name',
    'whereClauses' => ['name' => $_module_name . '.name',
        $_object_name . '_number' => $_module_name . '.' . $_object_name . '_number'],
    'searchInputs' => [$_module_name . '_number', 'name', 'priority', 'status'],

];
