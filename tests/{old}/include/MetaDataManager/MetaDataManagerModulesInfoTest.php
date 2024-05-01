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


class MetaDataManagerModulesInfoTest extends TestCase
{
    /**
     * @var TabController Instance of TabController
     */
    protected static $tabs;

    /**
     * @var array Test set of tabs for base app
     */
    protected static $testSystemTabs = [
        'Accounts' => 'Accounts',
        'Contacts' => 'Contacts',
        'Leads' => 'Leads',
        'Opportunities' => 'Opportunities',
        'Cases' => 'Cases',
        'Bugs' => 'Bugs',
    ];

    /**
     * @var array Test set of tabs for portal
     */
    protected static $testPortalTabs = [
        'Contacts' => 'Contacts',
        'Cases' => 'Cases',
        'Bugs' => 'Bugs',
    ];

    /**
     * @var array Store current system tabs to backup later
     */
    protected static $savedSystemTabs;

    /**
     * @var array Store current portal tabs to backup later
     */
    protected static $savedPortalTabs;

    /**
     * @var string Location of the mobile tabs metadata file
     */
    protected static $mobileMetaFile = 'include/MVC/Controller/wireless_module_registry.php';

    /**
     * @var string Location of the custom mobile tabs metadata file
     */
    protected static $customMobileMetaFile = 'custom/include/MVC/Controller/wireless_module_registry.php';

    /**
     * @var bool Flag to indicate if the mobile custom file already exists
     */
    protected static $mobileBackedUp = false;

    /**
     * @var bool Flag to indicate if the mobile custom path exists
     */
    protected static $mobileCustomPathExists = true;

    /**
     * @var string Path that is created for test purpose.
     */
    protected static $mobileCreatedPath;

    /**
     * Set up once before all tests are run
     */
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user', [true, 1]);

        self::$tabs = new TabController();

        // Save current system tabs and set test system tabs
        self::$savedSystemTabs = self::$tabs->get_system_tabs();
        self::$tabs->set_system_tabs(self::$testSystemTabs);

        // Save current portal tabs and set test portal tabs
        self::$savedPortalTabs = self::$tabs->getPortalTabs();
        self::$tabs->setPortalTabs(array_keys(self::$testPortalTabs));

        self::setUpMobile();
    }

    /**
     * Tear down once after all tests are run
     */
    public static function tearDownAfterClass(): void
    {
        // Reset saved system tabs
        self::$tabs->set_system_tabs(self::$savedSystemTabs);

        // Reset saved portal tabs
        self::$tabs->setPortalTabs(self::$savedPortalTabs);

        self::tearDownMobile();

        SugarTestHelper::tearDown();
    }

    protected function tearDown(): void
    {
        // Don't allow future tests to be affected by the cache that these tests yield.
        sugar_cache_clear('wireless_module_registry_keys');
    }

    /**
     * Test getModulesInfo method for the base app
     *
     * @group MetaDataManager
     */
    public function testBaseGetModulesInfo()
    {
        global $moduleList, $modInvisList;

        $fullModuleList = array_merge($moduleList, $modInvisList);

        // Run the test
        $mm = $this->getMockBuilder('MetaDataManager')
            ->setMethods(['getModulesData'])
            ->getMock();
        $mm->expects($this->any())
            ->method('getModulesData')
            ->will($this->returnValue([
                'Accounts' => [
                    'menu' => [
                        'quickcreate' => [
                            'meta' => [
                                'visible' => false,
                            ],
                        ],
                    ],
                ],
                'Cases' => [
                    'menu' => [
                        'quickcreate' => [
                            'meta' => [
                                'visible' => true,
                            ],
                        ],
                    ],
                ],
            ]));
        $expectedTabs = array_keys(self::$testSystemTabs);
        $expectedSubpanels = SubPanelDefinitions::get_all_subpanels();

        $modulesInfo = $mm->getModulesInfo();

        foreach ($fullModuleList as $module) {
            //Do not check modules that are ignored via ACL's
            if (!isset($modulesInfo[$module])) {
                continue;
            }
            // Test visible
            if (in_array($module, $moduleList)) {
                $this->assertTrue($modulesInfo[$module]['visible'], $module . ' should be visible');
            } else {
                $this->assertFalse($modulesInfo[$module]['visible'], $module . ' should be hidden');
            }

            // Test tabs
            if (in_array($module, $expectedTabs)) {
                $this->assertTrue($modulesInfo[$module]['display_tab'], $module . ' tab should be visible');
            } else {
                $this->assertFalse($modulesInfo[$module]['display_tab'], $module . ' tab should be hidden');
            }

            // Test subpanels
            if (in_array(strtolower($module), $expectedSubpanels)) {
                $this->assertTrue($modulesInfo[$module]['show_subpanels'], $module . ' subpanels should be visible');
            } else {
                $this->assertFalse($modulesInfo[$module]['show_subpanels'], $module . ' subpanels should be hidden');
            }

            // Test quickcreate
            if ($module === 'Cases') {
                $this->assertTrue($modulesInfo[$module]['quick_create'], $module . ' shortcut should be visible');
            } else {
                $this->assertFalse($modulesInfo[$module]['quick_create'], $module . ' shortcut should be hidden');
            }
        }
    }


    /**
     * Test getModulesInfo method for portal
     *
     * @group MetaDataManager
     */
    public function testPortalGetModulesInfo()
    {
        $browser = new SugarPortalBrowser();
        $browser->loadModules();

        // Run the test
        $mm = $this->getMockBuilder('MetaDataManager')
            ->setMethods(['getModulesData'])
            ->getMock();
        $mm->expects($this->any())
            ->method('getModulesData')
            ->will($this->returnValue([
                'Bugs' => [
                    'menu' => [
                        'quickcreate' => [
                            'meta' => [
                                'visible' => true,
                            ],
                        ],
                    ],
                ],
                'Cases' => [
                    'menu' => [
                        'quickcreate' => [
                            'meta' => [
                                'visible' => false,
                            ],
                        ],
                    ],
                ],
            ]));
        $expectedTabs = array_keys(self::$testPortalTabs);
        $expectedSubpanels = SubPanelDefinitions::get_all_subpanels();

        $modulesInfo = $mm->getModulesInfo();

        foreach ($browser->modules as $module => $moduleData) {
            // Test tabs
            if (in_array($module, $expectedTabs)) {
                $this->assertTrue($modulesInfo[$module]['display_tab'], $module . ' tab should be visible');
            } else {
                $this->assertFalse($modulesInfo[$module]['display_tab'], $module . ' tab should be hidden');
            }

            // Test subpanels
            if (in_array(strtolower($module), $expectedSubpanels)) {
                $this->assertTrue($modulesInfo[$module]['show_subpanels'], $module . ' subpanels should be visible');
            } else {
                $this->assertFalse($modulesInfo[$module]['show_subpanels'], $module . ' subpanels should be visible');
            }

            // Test quickcreate
            if ($module === 'Bugs') {
                $this->assertTrue($modulesInfo[$module]['quick_create'], $module . ' shortcut should be visible');
            } else {
                $this->assertFalse($modulesInfo[$module]['quick_create'], $module . ' shortcut should be hidden');
            }
        }
    }

    /**
     * Test getModulesInfo method for mobile
     *
     * @group MetaDataManager
     */
    public function testMobileGetModulesInfo()
    {
        // Run the test
        $mm = new MetaDataManagerMobile();
        $fullModuleList = $mm->getFullModuleList();
        $defaultEnabledModuleList = $mm->getDefaultEnabledModuleList();
        $expectedTabs = array_keys(self::$testSystemTabs);
        $expectedSubpanels = SubPanelDefinitions::get_all_subpanels();

        $modulesInfo = $mm->getModulesInfo();

        foreach ($fullModuleList as $module) {
            // Test tabs
            if (in_array($module, $defaultEnabledModuleList)) {
                $this->assertFalse($modulesInfo[$module]['display_tab'], $module . ' tab should be hidden');
            } else {
                $this->assertTrue($modulesInfo[$module]['display_tab'], $module . ' tab should be visible');
            }

            // Test subpanels
            if (in_array(strtolower($module), $expectedSubpanels)) {
                $this->assertTrue($modulesInfo[$module]['show_subpanels'], $module . ' subpanels should be visible');
            } else {
                $this->assertFalse($modulesInfo[$module]['show_subpanels'], $module . ' subpanels should be hidden');
            }

            // Test quickcreate
            if ($module === 'Cases' || $module === 'Contacts') {
                $this->assertTrue($modulesInfo[$module]['quick_create'], $module . ' shortcut should be visible');
            } else {
                $this->assertFalse($modulesInfo[$module]['quick_create'], $module . ' shortcut should be visible');
            }
        }
    }

    protected static function setUpMobile()
    {
        // Don't allow these tests to be affected by the cache.
        sugar_cache_clear('wireless_module_registry_keys');

        if (file_exists(self::$customMobileMetaFile)) {
            // Backup the custom file if there is one
            self::$mobileBackedUp = true;
            rename(self::$customMobileMetaFile, self::$customMobileMetaFile . '.backup');
        } elseif (!is_dir(dirname(self::$customMobileMetaFile))) {
            // If the custom path does not exist, we are gonna find the first
            // non existing folder of this path, se we can clean up later
            self::$mobileCustomPathExists = false;
            $customFolders = explode('/', dirname(self::$customMobileMetaFile));
            self::$mobileCreatedPath = '';
            foreach ($customFolders as $folder) {
                if (!empty(self::$mobileCreatedPath)) {
                    self::$mobileCreatedPath .= '/';
                }
                self::$mobileCreatedPath .= $folder;
                if (!is_dir(self::$mobileCreatedPath)) {
                    // This path does not exist. We'll have to start cleaning up
                    // from here.
                    break;
                }
            }
        }

        // Create a custom `wireless_module_registry.php` file
        // Module list must match self::$testSystemTabs
        $testFileContents = <<<EOF
<?php
\$wireless_module_registry = array(
	'Accounts' => array('disable_create' => true),
	'Contacts' => array(),
	'Leads' => array('disable_create' => true),
	'Opportunities' => array('disable_create' => true),
	'Cases' => array(),
	'Bugs' => array('disable_create' => true),
);
EOF;
        // If no custom file, need to create custom directory
        if (!self::$mobileBackedUp) {
            $filename = create_custom_directory(self::$mobileMetaFile);
        }

        // Create the custom file
        file_put_contents(self::$customMobileMetaFile, $testFileContents);
    }

    protected static function tearDownMobile()
    {
        // Reset backed-up custom file
        if (self::$mobileBackedUp) {
            rename(self::$customMobileMetaFile . '.backup', self::$customMobileMetaFile);
        } else {
            // Clean up custom path
            if (self::$mobileCustomPathExists) {
                unlink(self::$customMobileMetaFile);
            } else {
                rmdir_recursive(self::$mobileCreatedPath);
            }
        }
    }
}
