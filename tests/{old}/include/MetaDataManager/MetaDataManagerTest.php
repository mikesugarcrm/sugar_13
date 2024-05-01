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
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config;

class MetaDataManagerTest extends TestCase
{
    protected $mm;
    protected $mdc;
    protected $configBackup;

    protected function setUp(): void
    {
        SugarTestHelper::setup('beanFiles');
        SugarTestHelper::setup('beanList');
        SugarTestHelper::setup('current_user', [true, true]);
        SugarTestHelper::setup('files');

        $sugarConfig = \SugarConfig::getInstance();
        $sugarConfig->_cached_values = [];
        // Backup current language settings so manipulation can be tested
        $this->configBackup['languages'] = $GLOBALS['sugar_config']['languages'];
        if (isset($GLOBALS['sugar_config']['disabled_languages'])) {
            $this->configBackup['disabled_languages'] = $GLOBALS['sugar_config']['disabled_languages'];
        }
        $this->configBackup['activity_streams_enabled'] = $GLOBALS['sugar_config']['activity_streams_enabled'];

        $this->setTestLanguageSettings();
        $this->mm = MetaDataManager::getManager();
        $this->mdc = new MetaDataCache(DBManagerFactory::getInstance());
    }

    protected function tearDown(): void
    {
        MetaDataManager::enableCache();

        // Restore changed config stuff
        $GLOBALS['sugar_config']['languages'] = $this->configBackup['languages'];
        if (isset($this->configBackup['disabled_languages'])) {
            $GLOBALS['sugar_config']['disabled_languages'] = $this->configBackup['disabled_languages'];
        }
        $GLOBALS['sugar_config']['activity_streams_enabled'] = $this->configBackup['activity_streams_enabled'];

        MetaDataFiles::clearModuleClientCache();
        $this->mdc->reset();
        MetaDataManager::resetManagers();
        SugarTestHelper::tearDown();
        AuthenticationControllerMock::clearInstance();
    }

    public function testGetServerInfo()
    {
        // Server Info that should exist in all flavors and platforms
        $keys = [
            'flavor',
            'version',
            'build',
            'marketing_version',
            'product_name',
            'site_id',
            'portal_active',
        ];

        // Run the test
        $info = $this->mm->getServerInfo();
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $info);
        }
    }

    public function testGetAllLanguages()
    {
        $languages = $this->mm->getAllLanguages();

        $this->assertArrayHasKey('enabled', $languages, 'Enabled languages is missing.');
        $this->assertArrayHasKey('disabled', $languages, 'Disabled languages is missing.');
        $this->assertNotEmpty($languages['enabled'], 'Enabled languages is empty.');
        $this->assertNotEmpty($languages['disabled'], 'Disabled languages is empty');

        // Test content of each list
        $this->assertArrayHasKey(2, $languages['enabled'], 'Missing element of enabled languages');
        $this->assertArrayHasKey(1, $languages['disabled'], 'Missing element of disabled languages');
        $this->assertEquals('snazzy', $languages['enabled'][2], 'Incorrect value for disabled language 2');
        $this->assertEquals('br_ikea', $languages['disabled'][1], 'Incorrect value for disabled language 1');
    }

    /**
     * This is a functional test rather than a unit test.
     * This is due to MetaDataManager and MetaDataFiles not having
     * any tests.
     *
     * This test covers two scenarios,
     * the first one is if there exists no data, than we should expect
     * the metadata to not pick up any controllers.
     *
     * The second scenario covers metadata manager picking up on
     * provided collection / model controllers.
     */
    public function testFinalMetadataJSSource()
    {
        // Scenario 1
        // Create empty module with correct metadata structure.
        sugar_mkdir('modules/TestModule/clients/base/datas/model', 0700, true);

        $moduleMeta = $this->mm->getModuleDatas('TestModule');

        // We verify our assumptions that we should have an empty set of metadata.
        $this->assertArrayHasKey('_hash', $moduleMeta, 'Metadata does not contain a hash');
        $this->assertCount(1, $moduleMeta);

        // Clear our metadata cache.
        MetaDataFiles::clearModuleClientCache('TestModule');

        // Scenario 2
        // Add a model controller to our datas directory.
        sugar_touch('modules/TestModule/clients/base/datas/model/model.js');
        $moduleMeta = $this->mm->getModuleDatas('TestModule');

        // We now verify if we have additional controller metadata in our return.
        $this->assertArrayHasKey('model', $moduleMeta, 'Metadata does not contain a controller');
        $this->assertCount(2, $moduleMeta);

        // Clean up our test.
        MetaDataFiles::clearModuleClientCache('TestModule');
        unlink('modules/TestModule/clients/base/datas/model/model.js');
        rmdir_recursive('modules/TestModule/');
        SugarAutoLoader::buildCache();
    }

    protected function setTestLanguageSettings()
    {
        $GLOBALS['sugar_config']['languages'] = [
            'br_test' => 'Test Language',
            'br_mine' => 'My Language',
            'snazzy' => 'Snazzy Language',
            'whiskey' => 'Whiskey Language',
            'awesome' => 'Awesome Sauce',
            'br_ikea' => 'Ikead an idea',
            'en_us' => 'English (US)',
        ];

        $GLOBALS['sugar_config']['disabled_languages'] = 'whiskey,br_ikea';
    }

    /**
     * Provides data for testGetConfigs
     * @return array
     */
    public function getConfigsProvider()
    {
        return [
            'configWithIDMModeDisabled' => [
                'sugarConfig' => [
                    'processes_auto_validate_on_import' => true,
                    'processes_auto_validate_on_autosave' => true,
                    'processes_auto_save_interval' => 0,
                    'list_max_entries_per_page' => 1,
                    'max_record_fetch_size' => 2,
                    'mass_actions' => [
                        'mass_update_chunk_size' => 3,
                        'not_on_white_list' => 4,
                    ],
                    'analytics' => [
                        'enabled' => true,
                    ],
                    'idm_mode' => [],
                    'allowed_link_schemes' => ['http', 'https'],
                    'reports_complexity_display' => [],
                    'default_max_pinned_modules' => 4,
                ],
                'expectedConfig' => [
                    'autoValidateProcessesOnImport' => true,
                    'autoValidateProcessesOnAutosave' => true,
                    'processDesignerAutosaveInterval' => 0,
                    'maxQueryResult' => 1,
                    'maxRecordFetchSize' => 2,
                    'massActions' => [
                        'massUpdateChunkSize' => 3,
                    ],
                    'analytics' => [
                        'enabled' => true,
                    ],
                    'inboundEmailCaseSubjectMacro' => '[CASE:%1]',
                    'idmModeEnabled' => false,
                    'allowedLinkSchemes' => ['http', 'https'],
                    'csp' => "default-src 'self' 'unsafe-inline' 'unsafe-eval' *.sugarcrm.com *.salesfusion.com *.salesfusion360.com *.sugarapps.com *.sugarapps.eu *.sugarapps.com.au sugarcrm-release-archive.s3.amazonaws.com https://*.pendo.io pendo-io-static.storage.googleapis.com pendo-static-5197307572387840.storage.googleapis.com pendo-eu-static.storage.googleapis.com pendo-eu-static-5197307572387840.storage.googleapis.com *.bing.com *.virtualearth.net; connect-src 'self' wss://*.sugarapps.com wss://*.sugarapps.com.au wss://*.sugarapps.eu *.sugarcrm.com *.salesfusion.com *.salesfusion360.com *.sugarapps.com *.sugarapps.eu *.sugarapps.com.au sugarcrm-release-archive.s3.amazonaws.com https://*.pendo.io pendo-io-static.storage.googleapis.com pendo-static-5197307572387840.storage.googleapis.com pendo-eu-static.storage.googleapis.com pendo-eu-static-5197307572387840.storage.googleapis.com *.bing.com *.virtualearth.net; img-src data: http: https: blob:; object-src 'self'; frame-ancestors 'self'; font-src 'self' data: *.sugarcrm.com *.salesfusion.com *.salesfusion360.com *.sugarapps.com *.sugarapps.eu *.sugarapps.com.au sugarcrm-release-archive.s3.amazonaws.com https://*.pendo.io pendo-io-static.storage.googleapis.com pendo-static-5197307572387840.storage.googleapis.com pendo-eu-static.storage.googleapis.com pendo-eu-static-5197307572387840.storage.googleapis.com *.bing.com *.virtualearth.net",
                    'maxPinnedModules' => 4,
                    'customer_journey' => [],
                    'reports_complexity_display' => [],
                    'docusign' => [
                        'recipientSelection' => 'dont_show',
                    ],
                ],
            ],
            'configWithIDMModeEnable' => [
                'sugarConfig' => [
                    'processes_auto_validate_on_import' => true,
                    'processes_auto_validate_on_autosave' => true,
                    'processes_auto_save_interval' => 0,
                    'list_max_entries_per_page' => 1,
                    'max_record_fetch_size' => 2,
                    'mass_actions' => [
                        'mass_update_chunk_size' => 3,
                        'not_on_white_list' => 4,
                    ],
                    'analytics' => [
                        'enabled' => true,
                    ],
                    'idm_mode' => [
                        'enabled' => true,
                        'clientId' => 'testLocal',
                        'clientSecret' => 'testLocalSecret',
                        'stsUrl' => 'http://sts.sugarcrm.local',
                        'idpUrl' => 'http://login.sugarcrm.local',
                        'stsKeySetId' => 'KeySetName',
                        'tid' => 'srn:cloud:iam:eu:0000000001:tenant',
                        'idpServiceName' => 'iam',
                        'cloudConsoleUrl' => 'http://console.sugarcrm.local',
                        'cloudConsoleRoutes' => ['forgotPassword' => 'forgot-password'],
                    ],
                    'allowed_link_schemes' => ['http', 'https'],
                    'reports_complexity_display' => [],
                    'default_max_pinned_modules' => 4,
                ],
                'expectedConfig' => [
                    'autoValidateProcessesOnImport' => true,
                    'autoValidateProcessesOnAutosave' => true,
                    'processDesignerAutosaveInterval' => 0,
                    'maxQueryResult' => 1,
                    'maxRecordFetchSize' => 2,
                    'massActions' => [
                        'massUpdateChunkSize' => 3,
                    ],
                    'analytics' => [
                        'enabled' => true,
                    ],
                    'inboundEmailCaseSubjectMacro' => '[CASE:%1]',
                    'idmModeEnabled' => true,
                    'cloudConsoleForgotPasswordUrl' => 'http://console.sugarcrm.local/forgot-password/'
                        . urlencode('srn:cloud:iam:eu:0000000001:tenant') . '?tenant_hint='
                        . urlencode('srn:cloud:iam:eu:0000000001:tenant'),
                    'stsUrl' => 'http://sts.sugarcrm.local',
                    'tenant' => 'srn:cloud:iam:eu:0000000001:tenant',
                    'externalLoginSameWindow' => true,
                    'externalLogin' => true,
                    'allowedLinkSchemes' => ['http', 'https'],
                    'csp' => "default-src 'self' 'unsafe-inline' 'unsafe-eval' *.sugarcrm.com *.salesfusion.com *.salesfusion360.com *.sugarapps.com *.sugarapps.eu *.sugarapps.com.au sugarcrm-release-archive.s3.amazonaws.com https://*.pendo.io pendo-io-static.storage.googleapis.com pendo-static-5197307572387840.storage.googleapis.com pendo-eu-static.storage.googleapis.com pendo-eu-static-5197307572387840.storage.googleapis.com *.bing.com *.virtualearth.net; connect-src 'self' wss://*.sugarapps.com wss://*.sugarapps.com.au wss://*.sugarapps.eu *.sugarcrm.com *.salesfusion.com *.salesfusion360.com *.sugarapps.com *.sugarapps.eu *.sugarapps.com.au sugarcrm-release-archive.s3.amazonaws.com https://*.pendo.io pendo-io-static.storage.googleapis.com pendo-static-5197307572387840.storage.googleapis.com pendo-eu-static.storage.googleapis.com pendo-eu-static-5197307572387840.storage.googleapis.com *.bing.com *.virtualearth.net; img-src data: http: https: blob:; object-src 'self'; frame-ancestors 'self'; font-src 'self' data: *.sugarcrm.com *.salesfusion.com *.salesfusion360.com *.sugarapps.com *.sugarapps.eu *.sugarapps.com.au sugarcrm-release-archive.s3.amazonaws.com https://*.pendo.io pendo-io-static.storage.googleapis.com pendo-static-5197307572387840.storage.googleapis.com pendo-eu-static.storage.googleapis.com pendo-eu-static-5197307572387840.storage.googleapis.com *.bing.com *.virtualearth.net",
                    'maxPinnedModules' => 4,
                    'customer_journey' => [],
                    'reports_complexity_display' => [],
                    'docusign' => [
                        'recipientSelection' => 'dont_show',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param $sugarConfig
     * @param $expectedConfigs
     *
     * @throws ReflectionException
     * @dataProvider getConfigsProvider
     */
    public function testGetConfigs($sugarConfig, $expectedConfigs)
    {
        $administration = new Administration();
        $administration->retrieveSettings();
        if (!empty($administration->settings['system_name'])) {
            $expectedConfigs['systemName'] = $administration->settings['system_name'];
        }

        $manager = $this->createPartialMock('MetadataManagerMock', ['getSugarConfig', 'getIdpConfig', 'getTabList']);
        $manager->expects($this->any())
            ->method('getTabList')
            ->willReturn([]);

        $manager->expects($this->any())
            ->method('getSugarConfig')
            ->will($this->returnValue($sugarConfig));

        $configMock = $this->getMockBuilder(Config::class)
            ->setConstructorArgs([\SugarConfig::getInstance()])
            ->setMethods(['getIdmSettings', 'isIDMModeEnabled'])
            ->getMock();

        $idmSettingsMock = $this->getMockBuilder(\Administration::class)
            ->disableOriginalConstructor()
            ->getMock();

        if (isset($sugarConfig[Config::IDM_MODE_KEY])) {
            foreach ($sugarConfig[Config::IDM_MODE_KEY] as $key => $value) {
                $idmSettingsMock->settings[Config::IDM_MODE_KEY . '_' . $key] = $value;
            }
        }

        $configMock->expects($this->any())
            ->method('getIdmSettings')
            ->willReturn($idmSettingsMock);

        $idmEnabled = false;
        if (isset($sugarConfig[Config::IDM_MODE_KEY]['enabled']) && $sugarConfig[Config::IDM_MODE_KEY]['enabled']) {
            $idmEnabled = true;
        }
        $configMock->expects($this->any())
            ->method('isIDMModeEnabled')
            ->willReturn($idmEnabled);

        $manager->expects($this->any())
            ->method('getIdpConfig')
            ->will($this->returnValue($configMock));

        // Get the configs from metadata manager
        $actualConfigs = $manager->getConfigs();

        // Test that connectors are part of the config array
        $this->assertArrayHasKey('connectors', $actualConfigs);
        $this->assertNotEmpty($actualConfigs['connectors']);

        // Test that maps are part of the config array
        $this->assertArrayHasKey('maps', $actualConfigs);
        $this->assertNotEmpty($actualConfigs['maps']);

        // Remove connectors from the configs now, since testing data that could
        // change seems sorta not proper
        unset($actualConfigs['connectors'], $actualConfigs['versionMark']);

        // Remove maps from the configs now, since testing data that could
        // change seems sorta not proper
        unset($actualConfigs['maps']);

        // Run the actual config test
        $this->assertEquals($expectedConfigs, $actualConfigs);
    }

    public function testNormalizeMetadata()
    {
        // Test data, to be used for testing both mobile and base
        $data = [
            'modules' => [
                'Accounts' => [
                    'menu' => true,
                    'views' => [
                        'record' => true,
                    ],
                    'layouts' => [
                        'record' => true,
                    ],
                ],
            ],
        ];

        // Test base first, which should be equality
        $mm = MetaDataManager::getManager();
        $test = $mm->normalizeMetadata($data);
        $this->assertEquals($test, $data, 'Base data was manipulated and it should not have been');

        $mm = MetaDataManager::getManager('mobile');
        $test = $mm->normalizeMetadata($data);
        $this->assertNotEquals($test, $data, 'Mobile metadata was not manipulated and it should have been');
        $this->assertFalse(isset($test['modules']['Accounts']['menu']));
        $this->assertEmpty($test['modules']['Accounts']['views']);
        $this->assertEmpty($test['modules']['Accounts']['layouts']);
    }

    public function testGetAppListStrings()
    {
        $mm = MetaDataManager::getManager();
        $normalList = $mm->getAppListStrings('en_us');
        $tupleList = $mm->getAppListStrings('en_us', true);

        //Would be nice to mock the app_list_strings, but this currently isn't possible with return_app_list_strings_language
        $this->assertEquals(
            $normalList['checkbox_dom'],
            [
                '' => '',
                '1' => 'Yes',
                '2' => 'No',
            ]
        );

        $this->assertEquals(
            $tupleList['checkbox_dom'],
            [
                ['', ''],
                ['1', 'Yes'],
                ['2', 'No'],
            ]
        );
    }

    public function getLanguageDataProvider()
    {
        return [
            [
                [
                    'lang' => 'en_us',
                    'ordered' => true,
                ],
            ],
            [
                [
                    'lang' => 'en_us',
                    'ordered' => false,
                ],
            ],
        ];
    }

    /**
     * @group BR-1730
     * @group unit
     * @dataProvider getLanguageDataProvider
     */
    public function testGetLanguage($params)
    {
        $manager = $this->getMockBuilder('MetaDataManager')
            ->disableOriginalConstructor()->setMethods(['getAppListStrings', 'getLangUrl'])->getMock();
        //Skipping the constructor requires we set up the db ourselves
        $manager->db = DBManagerFactory::getInstance();

        $manager->expects($this->once())->method('getAppListStrings')
            ->with($params['lang'], $params['ordered'])->will($this->returnValue([]));

        $fileName = md5(microtime());

        $manager->expects($this->exactly(3))->method('getLangUrl')
            ->with($params['lang'], $params['ordered'])->will($this->returnValue($fileName));

        $manager->getLanguage($params);
    }

    /**
     * @dataProvider providerTestGetModuleView
     * @covers       MetaDataManager::getModuleView
     * @group unit
     */
    public function testGetModuleView($module, $view, $metadata, $expected)
    {
        $mm = $this->getMockBuilder('MetaDataManager')
            ->disableOriginalConstructor()
            ->setMethods(['getModuleViews'])
            ->getMock();

        $mm->expects($this->once())
            ->method('getModuleViews')
            ->with($this->equalTo($module))
            ->will($this->returnValue($metadata));

        $this->assertEquals($expected, $mm->getModuleView($module, $view));
    }

    public function providerTestGetModuleView()
    {
        return [
            // existing view
            [
                'Accounts',
                'record',
                ['record' => ['foo', 'bar']],
                ['foo', 'bar'],
            ],
            // non-existing view
            [
                'Accounts',
                'blaat',
                ['record' => ['foo', 'bar']],
                [],
            ],
        ];
    }

    /**
     * @dataProvider providerTestGetModuleViewFields
     * @covers       MetaDataManager::getModuleViewFields
     * @covers       MetaDataManager::getFieldNames
     * @group unit
     */
    public function testGetModuleViewFields($module, $view, $viewData, $dropdownViewData, $expectedFields, $expectedDisplayParams)
    {
        /** @var MetaDataManager|MockObject $mm */
        $mm = $this->getMockBuilder('MetaDataManager')
            ->disableOriginalConstructor()
            ->onlyMethods(['getModuleView', 'getModuleDropdownViewsForView'])
            ->getMock();

        $mm->expects($this->once())
            ->method('getModuleView')
            ->with($this->equalTo($module), $this->equalTo($view))
            ->will($this->returnValue($viewData));

        if (!empty($viewData)) {
            $mm->expects($this->once())
                ->method('getModuleDropdownViewsForView')
                ->with($this->equalTo($module), $this->equalTo($view))
                ->will($this->returnValue($dropdownViewData));
        }

        $fields = $mm->getModuleViewFields($module, $view, $displayParams);
        $this->assertEquals($expectedFields, $fields);
        $this->assertEquals($expectedDisplayParams, $displayParams);
    }

    public function providerTestGetModuleViewFields()
    {
        return [
            // empty view data
            [
                'Contacts',
                'record',
                [],
                [],
                [],
                [],
            ],
            // real view data
            [
                'Contacts',
                'record',
                [
                    'meta' => [
                        'panels' => [
                            [
                                'fields' => [
                                    // string based field def
                                    'first_name',

                                    // array based field def
                                    [
                                        'name' => 'last_name',
                                    ],

                                    // link field
                                    [
                                        'name' => 'tasks',
                                        'fields' => ['id', 'date_due'],
                                        'order_by' => 'date_due:desc',
                                    ],

                                    // array based invalid field
                                    [
                                        'span',
                                    ],

                                    // non-string/array invalid field
                                    69,

                                    // nested field set
                                    [
                                        'name' => 'primary_address',
                                        'fields' => [
                                            'street',
                                            [
                                                'name' => 'country',
                                            ],
                                        ],
                                    ],

                                    // anonymous nested field set
                                    [
                                        'fields' => [
                                            'foo',
                                            [
                                                'name' => 'bar',
                                            ],

                                            // link field inside field set
                                            [
                                                'name' => 'opportunities',
                                                'fields' => ['id', 'name'],
                                            ],
                                        ],
                                    ],

                                    // related field set
                                    [
                                        'related_fields' => [
                                            [
                                                'name' => 'good',
                                            ],
                                            'karma',

                                            // link field inside related fields
                                            [
                                                'name' => 'bugs',
                                            ],
                                        ],
                                    ],
                                    // link field as string
                                    'calls',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'lead_source' => [
                        'Cold Call' => [
                            'meta' => [
                                'panels' => [
                                    [
                                        'fields' => [
                                            'account_id',
                                            'birthdate',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'Existing Customer' => [
                            'meta' => [
                                'panels' => [
                                    [
                                        'fields' => [
                                            'portal_name',
                                        ],
                                    ],
                                    [
                                        'fields' => [
                                            'portal_active',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'first_name',
                    'last_name',
                    'tasks',
                    'primary_address',
                    'street',
                    'country',
                    'foo',
                    'bar',
                    'opportunities',
                    'good',
                    'karma',
                    'bugs',
                    'calls',
                    'account_id',
                    'birthdate',
                    'portal_name',
                    'portal_active',
                ],
                [
                    'first_name' => [],
                    'last_name' => [],
                    'tasks' => [
                        'fields' => ['id', 'date_due'],
                        'order_by' => 'date_due:desc',
                    ],
                    'street' => [],
                    'country' => [],
                    'primary_address' => [],
                    'foo' => [],
                    'bar' => [],
                    'opportunities' => [
                        'fields' => ['id', 'name'],
                    ],
                    'good' => [],
                    'karma' => [],
                    'bugs' => [],
                    'calls' => [],
                ],
            ],
        ];
    }

    public function testGetPlatformList()
    {
        SugarTestHelper::saveFile('custom/clients/platforms.php');
        SugarAutoLoader::ensureDir('custom/clients');

        $contents = <<<PLATFORMS
<?php
\$platforms[] = 'metadata-manager-test';
PLATFORMS;
        file_put_contents('custom/clients/platforms.php', $contents);

        SugarTestHelper::saveFile('custom/application/Ext/Platforms/platforms.ext.php');
        SugarAutoLoader::ensureDir('custom/application/Ext/Platforms');

        $contents = <<<PLATFORMS
<?php
\$platforms[] = 'extension-platform';
PLATFORMS;
        file_put_contents('custom/application/Ext/Platforms/platforms.ext.php', $contents);

        $platforms = MetaDataManager::getPlatformList();
        $this->assertContains('base', $platforms);
        $this->assertContains('mobile', $platforms);
        $this->assertContains('portal', $platforms);
        $this->assertContains('metadata-manager-test', $platforms);
        $this->assertContains('extension-platform', $platforms);
    }

    /**
     * @param array $input
     * @param array $expected
     *
     * @dataProvider removeDisabledFieldsProvider
     */
    public function testRemoveDisabledFields($input, $expected)
    {
        $mm = new MetaDataManager();
        $actual = SugarTestReflection::callProtectedMethod($mm, 'removeDisabledFields', [$input]);
        $this->assertSame($actual, $expected);
    }

    public static function removeDisabledFieldsProvider()
    {
        return [
            [
                [
                    'some-arbitrary-structure' => [
                        'fields' => [
                            [
                                'name' => 'f1',
                                'enabled' => true,
                            ],
                            [
                                'name' => 'f2',
                                'enabled' => false,
                            ],
                            [
                                'name' => 'f3',
                            ],
                            'f4',
                        ],
                    ],
                ],
                [
                    'some-arbitrary-structure' => [
                        'fields' => [
                            [
                                'name' => 'f1',
                                'enabled' => true,
                            ],
                            [
                                'name' => 'f3',
                            ],
                            'f4',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider cacheStaticProvider
     */
    public function testEnableCacheStatic($method, array $arguments, $dbMethod)
    {
        $db = $this->getCacheEnabledDatabaseMock($dbMethod);
        $this->callCacheStatic($db, $method, $arguments);
    }

    /**
     * @dataProvider cacheStaticProvider
     */
    public function testDisableCacheStatic($method, array $arguments, $dbMethod)
    {
        MetaDataManager::disableCache();
        $db = $this->getCacheDisabledDatabaseMock($dbMethod);
        $this->callCacheStatic($db, $method, $arguments);
    }

    private function callCacheStatic(DBManager $db, $method, array $arguments)
    {
        SugarTestHelper::setUp('mock_db', $db);
        SugarTestReflection::callProtectedMethod('MetaDataManager', $method, $arguments);
    }

    public static function cacheStaticProvider()
    {
        return [
            ['getPlatformsWithCachesInDatabase', [], 'getConnection'],
        ];
    }

    private function getCacheEnabledDatabaseMock($method)
    {
        $result = $this->getMockBuilder(\Doctrine\DBAL\Result::class)
            ->disableOriginalConstructor()
            ->setMethods(['fetchFirstColumn'])
            ->getMock();
        $result->expects($this->atLeastOnce())
            ->method('fetchFirstColumn')
            ->willReturn([]);

        $conn = $this->getMockBuilder(\Doctrine\DBAL\Connection::class)
            ->disableOriginalConstructor()
            ->setMethods(['executeQuery'])
            ->getMock();
        $conn->expects($this->atLeastOnce())
            ->method('executeQuery')
            ->willReturn($result);

        $db = $this->getAbstractDbManagerMock($method);
        $db->expects($this->atLeastOnce())
            ->method($method)
            ->willReturn($conn);

        return $db;
    }

    private function getCacheDisabledDatabaseMock($method)
    {
        $db = $this->getAbstractDbManagerMock($method);
        $db->expects($this->never())
            ->method($method);

        return $db;
    }

    protected function getAbstractDbManagerMock($method)
    {
        return $this->getMockBuilder('DBManager')
            ->setMethods([$method])
            ->getMockForAbstractClass();
    }

    /**
     * @dataProvider getPlatformsWithCachesInFilesystemProvider
     */
    public function testGetPlatformsWithCachesInFilesystem($fileName, $platformName)
    {
        $dir = 'cache/api/metadata';
        SugarTestHelper::saveFile($dir . '/' . $fileName);

        SugarAutoLoader::ensureDir($dir);
        file_put_contents($dir . '/' . $fileName, '');

        $platforms = SugarTestReflection::callProtectedMethod('MetaDataManager', 'getPlatformsWithCachesInFilesystem');
        $this->assertContains($platformName, $platforms);
    }

    public static function getPlatformsWithCachesInFilesystemProvider()
    {
        return [
            [
                'en_us_test_base_public_ordered.json',
                'base',
            ],
            [
                'en_us_test_portal_public.json',
                'portal',
            ],
            [
                'metadata_test_mobile_private.php',
                'mobile',
            ],
        ];
    }

    /**
     * @dataProvider getPlatformsWithCachesInDatabaseProvider
     */
    public function testGetPlatformsWithCachesInDatabase($key, $expected)
    {
        $this->assertNotEmpty($expected);
        $this->mdc->set($key, true);

        $platforms = SugarTestReflection::callProtectedMethod('MetaDataManager', 'getPlatformsWithCachesInDatabase');
        foreach ($expected as $platform) {
            $this->assertContains($platform, $platforms);
        }
    }

    public static function getPlatformsWithCachesInDatabaseProvider()
    {
        return [
            [
                'meta:hash:public:base',
                [
                    'base',
                ],
            ],
            [
                'meta:hash:base,mobile',
                [
                    'base',
                    'mobile',
                ],
            ],
            [
                'meta:hash:contexthash1234:base,custom_platform-with_underscores-and_dashes',
                [
                    'base',
                    'custom_platform-with_underscores-and_dashes',
                ],
            ],
        ];
    }

    /**
     * @dataProvider getCachedMetadataHashKeyProvider
     *
     * @param bool $public
     * @param array $platforms
     * @param string $contextHash
     * @param string $expected
     */
    public function testGetCachedMetadataHashKey($public, $platforms, $contextHash, $expected)
    {
        $contextMock = $this->createPartialMock('MetaDataContextDefault', ['getHash']);
        $contextMock->method('getHash')->willReturn($contextHash);

        $mm = new MetaDataManager($platforms, $public);
        $cacheKey = SugarTestReflection::callProtectedMethod($mm, 'getCachedMetadataHashKey', [$contextMock]);

        $this->assertEquals($expected, $cacheKey);
    }

    public static function getCachedMetadataHashKeyProvider()
    {
        return [
            [
                true,
                ['base'],
                null,
                'meta:hash:public:base',
            ],
            [
                false,
                ['base', 'mobile'],
                null,
                'meta:hash:base,mobile',
            ],
            [
                true,
                ['base', 'mobile'],
                'contextHash123',
                'meta:hash:public:base,mobile',
            ],
            [
                false,
                ['base', 'mobile'],
                'contextHash123',
                'meta:hash:contextHash123:base,mobile',
            ],
        ];
    }

    public function getActivityStreamDataProvider()
    {
        $moduleName = 'Meetings';

        return [
            'It should assert true when config\'s activity_streams_enabled is set to true and activity is enabled for module' => [
                $moduleName,
                true,
                true,
            ],
            'It should assert false when config\'s activity_streams_enabled is set to false regardless of module' => [
                $moduleName,
                false,
                false,
            ],
        ];
    }

    /**
     * @dataProvider getActivityStreamDataProvider
     *
     * @param $moduleName
     * @param $isActivityStreamEnabled
     * @param $expected
     */
    public function testActivityStreamDataWhenConfigIsEnabledAndDisabled($moduleName, $isActivityStreamEnabled, $expected)
    {
        Activity::enable();

        $GLOBALS['sugar_config']['activity_streams_enabled'] = $isActivityStreamEnabled;
        \SugarConfig::getInstance()->clearCache('activity_streams_enabled');
        $data = $this->mm->getModuleData($moduleName);

        Activity::restoreToPreviousState();

        $this->assertEquals($expected, $data['activityStreamEnabled']);
    }

    public function testGetFilterModulesFlag()
    {
        $mm = $this->createPartialMock('MetaDataManager', []);
        $defualtContext = SugarTestReflection::callProtectedMethod($mm, 'getDefaultContext');
        $this->assertFalse(SugarTestReflection::callProtectedMethod($mm, 'getFilterModulesFlag', [$defualtContext]));

        $this->assertTrue(SugarTestReflection::callProtectedMethod($mm, 'getFilterModulesFlag'));
        $userContextMock = $this->createPartialMock('MetaDataContextUser', []);
        $this->assertTrue(SugarTestReflection::callProtectedMethod($mm, 'getFilterModulesFlag', [$userContextMock]));
    }


    /**
     * @dataProvider getEditableDropdownFilterProvider
     */
    public function testGetEditableDropdownFilter($filter, $defaults, $expected)
    {
        global $app_list_strings;


        $app_list_strings['md_fix_filter_test'] = $defaults;

        $mock = $this->createPartialMock('MetaDataManager', ['getRawFilter']);
        $mock->expects($this->any())->method('getRawFilter')->willReturn($filter);
        $actual = $mock->getEditableDropdownFilter('md_fix_filter_test', 'foo');

        $this->assertEquals($expected, $actual);
        //Also assert that the JSON version will encode correctly. php equivalent is not enough
        $this->assertEquals(json_encode($expected), json_encode($actual));

        unset($app_list_strings['md_fix_filter_test']);
    }

    public static function getEditableDropdownFilterProvider()
    {
        return [
            //Arrays with numeric keys
            [
                [],
                [
                    '01' => 'one',
                    '02' => 'two',
                    '10' => 'ten',
                ],
                [
                    '01' => true,
                    '02' => true,
                    '10' => true,
                ],
            ],
            //Non-empty filter should not contain new values by default
            //nor entries that were in the filter but not the default
            [
                [
                    'a' => false,
                    'b' => true,
                    'c' => true,
                ],
                [
                    'a' => 'A',
                    'c' => 'C',
                    'd' => 'D',
                ],
                [
                    'a' => false,
                    'c' => true,
                    'd' => false,
                ],
            ],
            //Order should be preserved from the filter
            [
                [
                    'b' => true,
                    'a' => true,
                    'c' => false,
                ],
                [
                    'a' => 'A',
                    'b' => 'B',
                    'c' => 'C',
                ],
                [
                    'b' => true,
                    'a' => true,
                    'c' => false,
                ],
            ],
        ];
    }

    public function testGetPartialMetadata()
    {
        $mm = $this->createPartialMock('MetaDataManager', ['loadSectionMetadata']);
        $contextSections = SugarTestReflection::callProtectedMethod($mm, 'getContextAwareSections');
        $allSections = array_merge($contextSections, ['foo']);
        SugarTestReflection::setProtectedValue($mm, 'sections', $allSections);
        $mm->expects($this->once())
            ->method('loadSectionMetadata')
            ->with('foo')
            ->willReturn(['foo' => 'bar']);

        $data = SugarTestReflection::callProtectedMethod($mm, 'loadMetadata', [[], new MetaDataContextPartial()]);

        unset($data['_hash']);
        unset($data['_override_values']);

        $this->assertSame(['foo' => 'bar'], $data);
    }

    /**
     * @covers ::getDefaultTimelineModules
     */
    public function testGetDefaultTimelineModules()
    {
        $mmMock = $this->createPartialMock(
            \MetaDataManager::class,
            ['getSubpanelMetadata']
        );
        $mmMock->method('getSubpanelMetadata')->willReturn([
            'Accounts' => [
                'base' => [
                    'layout' => [
                        'subpanels' => [
                            'components' => [
                                [
                                    'layout' => 'subpanel',
                                    'label' => 'LBL_CALLS_SUBPANEL_TITLE',
                                    'context' => [
                                        'link' => 'calls',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $bean = BeanFactory::getBean('Accounts');
        $result = $mmMock->getDefaultTimelineModules($bean);
        $this->assertEquals('Calls', $result['calls']);
    }
}

class MetadataManagerMock extends MetaDataManager
{
    public function getConfigs()
    {
        return parent::getConfigs();
    }
}

class AuthenticationControllerMock extends AuthenticationController
{
    public static function clearInstance(): void
    {
        parent::$authcontrollerinstance = null;
    }
}
