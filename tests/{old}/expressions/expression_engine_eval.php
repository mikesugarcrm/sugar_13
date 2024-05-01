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
//change directories to where this file is located.
//this is to make sure it can find dce_config.php
chdir('../../');

require_once 'include/entryPoint.php';


if (!empty($_REQUEST['expression'])) {
    $admin = new User();
    $admin = $admin->retrieve(1);
    global $current_user, $timezones;
    $current_user = $admin;

    try {
        $expression = Parser::evaluate(from_html($_REQUEST['expression']));
        print_r($expression->evaluate());
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
