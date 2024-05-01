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

// constant to indicate that we are running tests
define('SUGAR_PHPUNIT_RUNNER', true);

// prevent ext/session from trying to send headers, since it doesn't make sense in CLI mode
// and will conflict with PHPUnit output
ini_set('session.use_cookies', '0');
session_cache_limiter(false);

set_include_path(__DIR__ . PATH_SEPARATOR . get_include_path());

// move current working directory to the web root directory
chdir(__DIR__ . '/../..');

error_reporting(E_ALL & ~E_DEPRECATED);

TestDependencyInjectionHelper::resetContainer();

// initialize the various globals we use
global $beanFiles;
global $beanList;
global $bwcModules;
global $current_language;
global $current_user;
global $locale;
global $modInvisList;
global $moduleList;
global $objectList;
global $sugar_config;
global $sugar_flavor;
global $sugar_version;
require_once 'include/entryPoint.php';

$current_language = $sugar_config['default_language'];
// disable the SugarLogger
$sugar_config['logger']['level'] = 'fatal';

$GLOBALS['sugar_config']['default_permissions'] = [
    'dir_mode' => 02770,
    'file_mode' => 0777,
    'chown' => '',
    'chgrp' => '',
];

$GLOBALS['js_version_key'] = 'testrunner';

// helps silence the license checking when running unit tests.
$_SESSION['VALIDATION_EXPIRES_IN'] = 'valid';

// clean out the cache directory
$repair = new RepairAndClear();
$repair->module_list = [];
$repair->show_output = false;
$repair->clearJsLangFiles();
$repair->clearJsFiles();

$focus = Administration::getSettings();

// make sure the client license has been validated
$focus->saveSetting('license', 'vk_end_date', date('Y-m-d', strtotime('+1 year')));

// mark that we got by the admin wizard already
$focus->saveSetting('system', 'adminwizard', 1);

// custom helper support
SugarAutoLoader::requireWithCustom('tests/SugarTestHelperInclude.php');

$GLOBALS['db']->commit();

// Disables sending email.
define('DISABLE_EMAIL_SEND', true);
