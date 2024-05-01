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

include_once 'modules/ConsoleConfiguration/clients/base/api/ConsoleConfigModuleApi.php';

/**
 * @coversDefaultClass ConsoleConfigModuleApi
 */
class ConsoleConfigModuleApiTest extends TestCase
{
    protected $consoleConfigApi;
    protected $accountMetaFile = 'custom/modules/Accounts/clients/base/views/multi-line-list/multi-line-list.php';
    protected $caseMetaFile = 'custom/modules/Cases/clients/base/views/multi-line-list/multi-line-list.php';

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, true]);
        SugarTestHelper::setUp('app_list_strings');
        $this->consoleConfigApi = new ConsoleConfigMock();
    }

    protected function tearDown(): void
    {
        foreach (['Accounts', 'Cases'] as $mod) {
            $filename = 'custom/modules/' . $mod . '/clients/base/views/multi-line-list/multi-line-list.php';
            if ($this->deleteTestFile($filename)) {
                MetaDataFiles::clearModuleClientCache($mod, 'view');
                TemplateHandler::clearCache($mod);
            }
        }
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::configSaveLabels
     */
    public function testConfigSaveLabels()
    {
        // begin test data
        $args = ['module' => 'ConsoleConfiguration'];
        $args['labels'] = [];
        $args['labels']['Accounts'] = [];
        $args['labels']['Cases'] = [];
        $args['labels']['Accounts'][] = ['label' => 'LBL_CONSOLE_CONFIG_TEST_0', 'labelValue' => 'account cct0',];
        $args['labels']['Accounts'][] = ['label' => 'LBL_CONSOLE_CONFIG_TEST_1', 'labelValue' => 'account cct1',];
        $args['labels']['Cases'][] = ['label' => 'LBL_CONSOLE_CONFIG_TEST_0', 'labelValue' => 'case cct0',];
        //end test data

        $this->consoleConfigApi->configSaveLabels($args);

        $accountsString = return_module_language($GLOBALS['current_language'], 'Accounts', true);
        $casesString = return_module_language($GLOBALS['current_language'], 'Cases', true);

        $this->assertSame('account cct0', $accountsString['LBL_CONSOLE_CONFIG_TEST_0']);
        $this->assertSame('account cct1', $accountsString['LBL_CONSOLE_CONFIG_TEST_1']);
        $this->assertSame('case cct0', $casesString['LBL_CONSOLE_CONFIG_TEST_0']);
    }

    /**
     * @covers ::configSaveMetaFiles
     */
    public function testConfigSaveMetaFiles()
    {
        $viewdefs = [];
        // begin test data
        $args = ['module' => 'ConsoleConfiguration'];
        $args['viewdefs'] = [];
        $args['viewdefs']['Cases'] = [
            'base' => [
                'view' => [
                    'multi-line-list' => [
                        'panels' => [
                            [
                                'label' => 'LBL_PANEL_1',
                                'fields' => [
                                    [
                                        'name' => 'case_number',
                                        'label' => 'LBL_AGENT_WORKBENCH_NUMBER',
                                        'width' => 'xsmall',
                                        'subfields' => [
                                            [
                                                'name' => 'case_number',
                                                'label' => 'LBL_AGENT_WORKBENCH_NUMBER',
                                                'default' => true,
                                                'enabled' => true,
                                                'readonly' => true,
                                            ],
                                        ],
                                    ],
                                    [
                                        'name' => 'status',
                                        'label' => 'LBL_AGENT_WORKBENCH_PRIORITY_STATUS',
                                        'width' => 'small',
                                        'subfields' => [
                                            [
                                                'name' => 'priority',
                                                'label' => 'LBL_LIST_PRIORITY',
                                                'default' => true,
                                                'enabled' => true,
                                                'type' => 'enum',
                                            ],
                                            [
                                                'name' => 'status',
                                                'label' => 'LBL_STATUS',
                                                'default' => true,
                                                'enabled' => true,
                                                'type' => 'case-status',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $args['viewdefs']['Accounts'] = [
            'base' => [
                'view' => [
                    'multi-line-list' => [
                        'panels' => [
                            [
                                'label' => 'LBL_PANEL_1',
                                'fields' => [
                                    [
                                        'name' => 'name',
                                        'label' => 'LBL_RENEWALS_CONSOLE_ACCOUNT_NAME_INDUSTRY',
                                        'width' => 'xlarge',
                                        'subfields' => [
                                            [
                                                'name' => 'name',
                                                'label' => 'LBL_LIST_ACCOUNT_NAME',
                                                'width' => 'large',
                                            ],
                                            [
                                                'name' => 'industry',
                                                'label' => 'LBL_INDUSTRY',
                                                'default' => true,
                                                'enabled' => true,
                                                'readonly' => true,
                                                'type' => 'enum',
                                            ],
                                        ],
                                    ],
                                    [
                                        'name' => 'description',
                                        'label' => 'LBL_DESCRIPTION',
                                        'subfields' => [
                                            [
                                                'name' => 'description',
                                                'label' => 'LBL_DESCRIPTION',
                                                'default' => true,
                                                'enabled' => true,
                                                'sortable' => false,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        //end test data

        $this->deleteTestFile($this->accountMetaFile);
        $this->deleteTestFile($this->caseMetaFile);

        $this->consoleConfigApi->configSaveMetaFiles($args);

        $this->assertTrue(file_exists($this->accountMetaFile));
        $this->assertTrue(file_exists($this->caseMetaFile));

        require "$this->accountMetaFile";
        $this->assertEquals($args['viewdefs']['Accounts'], $viewdefs['Accounts']);

        require "$this->caseMetaFile";
        $this->assertEquals($args['viewdefs']['Cases'], $viewdefs['Cases']);
    }

    /**
     * @param $filename
     * @return bool
     */
    protected function deleteTestFile($filename): bool
    {
        if (file_exists($filename)) {
            unlink($filename);
            return true;
        }
        return false;
    }
}

/**
 * Class ConsoleConfigMock
 */
class ConsoleConfigMock extends ConsoleConfigModuleApi
{
    public function configSaveLabels(array $args)
    {
        parent::configSaveLabels($args);
    }

    public function configSaveMetaFiles(array $args, string $viewName = 'multi-line-list')
    {
        parent::configSaveMetaFiles($args);
    }
}
