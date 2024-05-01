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

use Sugarcrm\Sugarcrm\Security\Validator\Validator;
use PHPUnit\Framework\TestCase;

class Bugs39819_39820Test extends TestCase
{
    /**
     * @ticket 39819
     * @ticket 39820
     */
    protected function setUp(): void
    {
        SugarAutoLoader::ensureDir('custom/modules/Accounts/language'); // Creating nested directories at a glance
        SugarTestHelper::setUp('mod_strings', ['Administration']);
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testLoadEnHelp()
    {
        // en_us help on a standard module.
        file_put_contents('modules/Accounts/language/en_us.help.DetailView.html', '<h1>ENBugs39819-39820</h1>');

        $_SERVER['HTTP_HOST'] = '';
        $_SERVER['SCRIPT_NAME'] = '';
        $_SERVER['QUERY_STRING'] = '';

        $_REQUEST['view'] = 'documentation';
        $_REQUEST['lang'] = 'en_us';
        $_REQUEST['help_module'] = 'Accounts';
        $_REQUEST['help_action'] = 'DetailView';

        ob_start();
        require 'modules/Administration/SupportPortal.php';

        $tStr = ob_get_contents();
        ob_end_clean();

        unlink('modules/Accounts/language/en_us.help.DetailView.html');

        // I expect to get the en_us normal help file....
        $this->assertMatchesRegularExpression("/.*ENBugs39819\-39820.*/", $tStr);
    }

    public function testLoadCustomItHelp()
    {
        // Custom help (NOT en_us) on a standard module.
        file_put_contents('custom/modules/Accounts/language/it_it.help.DetailView.html', '<h1>Bugs39819-39820</h1>');

        // Register language and reinit InputValidation
        $GLOBALS['sugar_config']['languages']['it_it'] = 'Italian';
        SugarConfig::getInstance()->clearCache('languages');
        Validator::clearValidatorsCache();

        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['QUERY_STRING'] = 'module=Administration&action=index';

        $_REQUEST['view'] = 'documentation';
        $_REQUEST['lang'] = 'it_it';
        $_REQUEST['help_module'] = 'Accounts';
        $_REQUEST['help_action'] = 'DetailView';

        ob_start();
        require 'modules/Administration/SupportPortal.php';

        $tStr = ob_get_contents();
        ob_end_clean();

        // Cleanups custom language
        unlink('custom/modules/Accounts/language/it_it.help.DetailView.html');
        unset($GLOBALS['sugar_config']['languages']['it_it']);

        // I expect to get the it_it custom help....
        $this->assertMatchesRegularExpression("/.*Bugs39819\-39820.*/", $tStr);

        // check for encoded URL in mailto body BR-3545. Change done in SupportPortal.tpl
        $this->assertMatchesRegularExpression("/body=http%3A%2F%2Flocalhost%2Findex\.php%3Fmodule%3DAdministration%26action%3Dindex/", $tStr);
    }
}
