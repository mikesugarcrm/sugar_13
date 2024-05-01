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

use PHPUnit\Framework\TestCase;

/**
 * @ticket 46740
 */
class Bug46740Test extends TestCase
{
    /**
     * Language used to perform the test
     *
     * @var string
     */
    protected $language;

    /**
     * Module to be renamed
     *
     * @var string
     */
    protected $module = 'Contracts';

    /**
     * Module name translation
     *
     * @var string
     */
    protected $translation = 'ContractsBug46740Test';

    /**
     * Temporary file path
     *
     * @var string
     */
    protected $file = null;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * Generates custom module localization file
     */
    protected function setUp(): void
    {
        SugarTestHelper::setUp('moduleList');
        global $sugar_config;
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('current_user');
        $this->language = $sugar_config['default_language'];

        // create custom localization file
        $this->file = 'custom/include/language/' . $this->language . '.lang.php';

        if (file_exists($this->file)) {
            rename($this->file, $this->file . '.bak');
        }

        $dirName = dirname($this->file);
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }

        $contents = <<<FILE
<?php
\$app_list_strings["moduleList"]["{$this->module}"] = "{$this->translation}";
FILE;

        file_put_contents($this->file, $contents);
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * Removes custom module localization file
     */
    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        if (file_exists($this->file . '.bak')) {
            rename($this->file . '.bak', $this->file);
        } else {
            unlink($this->file);
        }
        SugarTestHelper::tearDown();
    }

    /**
     * Tests that custom module localization data is used
     */
    public function testCustomModuleLocalizationIsUsed()
    {
        // This test ensures that if the user changes the name of the module Contracts,
        // that name change is updated for the Admin page as well. For some reason, Contracts is the only
        // module to do so (not Opportunities, etc.). With the new Admin page, the label is retrieved
        // via Admin's language file and not the app's module list. Moving forward, if the user changes
        // the name of module Contracts, the Admin page label will not update. In order to update the label,
        // the user needs to modify the Admin custom language file
        $this->markTestSkipped();

        global $sugar_flavor, $server_unique_key, $current_language;
        $app_list_strings = return_app_list_strings_language($this->language, false);

        $admin_group_header = [];
        require 'modules/Administration/metadata/adminpaneldefs.php';

        $found = false;
        foreach ($admin_group_header as $header) {
            $headerGroup = array_shift($header);
            if ($headerGroup === $this->translation) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);
    }
}
