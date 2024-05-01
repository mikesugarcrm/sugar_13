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

class ConnectorsEnableDisableTest extends Sugar_Connectors_TestCase
{
    /**
     * Listing of files created by this test. These should be trackes and cleaned
     * up after each test so they do not affect downstream tests.
     *
     * @var array
     */
    protected $backupFiles = [
        'custom/modules/Connectors/connectors/sources/ext/rest/twitter/config.php',
        'custom/modules/Connectors/connectors/sources/ext/rest/twitter/mapping.php',
        'custom/modules/Connectors/connectors/sources/ext/eapm/webex/config.php',
        'custom/modules/Connectors/connectors/sources/ext/eapm/webex/mapping.php',
        'custom/modules/Connectors/metadata/connectors.php',
        'custom/modules/Connectors/metadata/display_config.php',
        'custom/modules/Connectors/metadata/searchdefs.php',
    ];

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user', [true, 1]);
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('files');
        SugarTestHelper::saveFile($this->backupFiles);
        $this->removeCustomConnectorFiles();
    }

    protected function tearDown(): void
    {
        // This tears down all of the setup items and restores backed up files
        SugarTestHelper::tearDown();
    }

    /**
     * Removes existing custom connector files so that the test runs as an OOTB
     * instance.
     */
    public function removeCustomConnectorFiles()
    {
        foreach ($this->backupFiles as $file) {
            @unlink($file);
        }
    }

    public function testEnableAll()
    {
        $_REQUEST['display_values'] = 'ext_rest_twitter:Accounts,ext_rest_twitter:Leads';
        $_REQUEST['display_sources'] = 'ext_rest_twitter';
        $_REQUEST['action'] = 'SaveModifyDisplay';
        $_REQUEST['module'] = 'Connectors';
        $_REQUEST['from_unit_test'] = true;

        $controller = new ConnectorsController();
        $controller->action_SaveModifyDisplay();

        require CONNECTOR_DISPLAY_CONFIG_FILE;

        foreach ($modules_sources as $module => $entries) {
            if ($module == 'Accounts' || $module == 'Contacts') {
                $this->assertTrue(in_array('ext_rest_twitter', $entries));
            }
        }
    }

    public function testDisableAll()
    {
        $modules_sources = [];
        $controller = new ConnectorsController();

        $_REQUEST['display_values'] = '';
        $_REQUEST['display_sources'] = 'ext_rest_twitter';
        $_REQUEST['action'] = 'SaveModifyDisplay';
        $_REQUEST['module'] = 'Connectors';
        $_REQUEST['from_unit_test'] = true;

        $controller->action_SaveModifyDisplay();

        require CONNECTOR_DISPLAY_CONFIG_FILE;
        $this->assertTrue(empty($modules_sources['ext_rest_twitter']));
    }

    public function testToggleEAPM()
    {
        $controller = new ConnectorsController();

        // Needed as arguments for the test
        $connectors = ConnectorUtils::getConnectors(true);
        $sources = [
            'ext_rest_twitter' => 'ext_rest_twitter',
            'ext_eapm_webex' => 'ext_eapm_webex',
        ];

        // Run the method being tested
        $controller->handleEAPMSettings($connectors, $sources, []);

        // Get our results
        $results = [
            ConnectorUtils::eapmEnabled($sources['ext_rest_twitter']),
            ConnectorUtils::eapmEnabled($sources['ext_eapm_webex']),
        ];

        // Make round 1 of assertions
        $this->assertFalse($results[0], 'Failed to disable Twitter');
        $this->assertFalse($results[1], 'Failed to disable WebEx');

        // Begin second phase of the test, starting with fresh connector data
        $connectors = ConnectorUtils::getConnectors(true);

        // Mocks the $_REQUEST array used in the tested method
        $request = [
            'ext_rest_twitter_external' => 1,
            'ext_eapm_webex_external' => 1,
        ];

        // Run the method being tested
        $controller->handleEAPMSettings($connectors, $sources, $request);

        // Get the results
        $results = [
            ConnectorUtils::eapmEnabled($sources['ext_rest_twitter']),
            ConnectorUtils::eapmEnabled($sources['ext_eapm_webex']),
        ];

        // Make assertions
        $this->assertTrue($results[0], 'Failed to enable Twitter');
        $this->assertTrue($results[1], 'Failed to enable WebEx');
    }
}
