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

use Sugarcrm\SugarcrmTestsUnit\TestDependencyInjectionHelper;

define('sugarEntry', true);
chdir(SUGAR_BASE_DIR);

// Disables sending email.
define('DISABLE_EMAIL_SEND', true);

error_reporting(E_ALL & ~E_DEPRECATED);

TestDependencyInjectionHelper::resetContainer();
