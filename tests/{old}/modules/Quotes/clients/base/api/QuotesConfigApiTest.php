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

namespace Sugarcrm\SugarcrmTestUnit\modules\Quotes\clients\base\api;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\MetaData\ViewdefManager;

/**
 * @coversDefaultClass \QuotesConfigApi
 */
class QuotesConfigApiTest extends TestCase
{
    protected $oldConfig = [];
    protected $moduleList = [];
    protected $oldStrings = [];

    protected function setUp(): void
    {
        $sugar_config = null;
        $app_strings = null;
        require 'config.php';
        require 'include/language/en_us.lang.php';


        if (array_key_exists('sugar_config', $GLOBALS)) {
            $this->oldConfig = $GLOBALS['sugar_config'];
        }

        if (array_key_exists('moduleList', $GLOBALS)) {
            $this->moduleList = $GLOBALS['moduleList'];
        }

        if (array_key_exists('app_strings', $GLOBALS)) {
            $this->moduleList = $GLOBALS['app_strings'];
        }

        $GLOBALS['sugar_config'] = $sugar_config;
        $GLOBALS['moduleList'] = [];
        $GLOBALS['app_strings'] = $app_strings;
    }

    protected function tearDown(): void
    {
        $GLOBALS['sugar_config'] = $this->oldConfig;
        $GLOBALS['moduleList'] = $this->moduleList;
        $GLOBALS['app_strings'] = $this->oldStrings;

        \SugarTestConfigUtilities::resetConfig();
    }

    /**
     * @covers ::configSave
     */
    public function testConfigSave()
    {
        $api = new \RestService();
        $userMock = $this->getMockBuilder('\User')
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $userMock->expects($this->once())
            ->method('isAdmin')
            ->will($this->returnValue(true));

        $api->user = $userMock;
        $args = [
            'module' => 'Quotes',
            'foo' => 'bar',
            'worksheet_columns' => [],
            'worksheet_columns_related_fields' => [],
            'summary_columns' => [],
            'summary_columns_related_fields' => [],
            'footer_rows' => [],
            'footer_rows_related_fields' => [],
        ];

        $mock = $this->getMockBuilder('\QuotesConfigApi')
            ->setMethods(['applyWorksheetColumnsConfig'])
            ->disableOriginalConstructor()
            ->getMock();

        $refObject = new \ReflectionClass($mock);
        $refProp = $refObject->getProperty('skipMetadataRefresh');
        $refProp->setAccessible(true);
        $refProp->setValue($mock, true);

        $mock->expects($this->once())
            ->method('applyWorksheetColumnsConfig');

        $config = $mock->configSave($api, $args);
        $this->assertEquals('bar', $config['foo']);
    }

    /**
     * @param array $settings
     * @covers ::applyWorksheetColumnsConfig
     * @dataProvider applyConfigProvider
     */
    public function testApplyWorksheetColumnsConfig($settings, $expectException)
    {
        $quotesViewdef = [];
        $quotesViewdef['panels'][0]['fields'][1]['related_fields'][0]['fields'] = [
            'name' => 'product_bundle_items',
            'fields' => [],
        ];
        $viewdefManagerMock = $this->getMockBuilder(ViewdefManager::class)
            ->setMethods(['loadViewdef', 'saveViewdef'])
            ->disableOriginalConstructor()
            ->getMock();

        $viewdefManagerMock->expects($this->any())
            ->method('loadViewdef')
            ->will($this->returnValue($quotesViewdef));

        $mock = $this->getMockBuilder('\QuotesConfigApi')
            ->setMethods(['getViewdefManager', 'getSettings'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('getSettings')
            ->will($this->returnValue($settings));

        $mock->expects($this->any())
            ->method('getViewdefManager')
            ->will($this->returnValue($viewdefManagerMock));

        if ($expectException) {
            $this->expectException(\SugarApiExceptionInvalidParameter::class);
        } else {
            $viewdefManagerMock->expects($this->exactly(4))
                ->method('saveViewdef');
        }

        $mock->applyWorksheetColumnsConfig();
    }

    public function applyConfigProvider()
    {
        return [
            [
                [
                    'worksheet_columns_related_fields' => [],
                ], true,
            ],
            [
                [
                    'worksheet_columns' => '',
                    'worksheet_columns_related_fields' => [],
                ], true,
            ],
            [
                [
                    'worksheet_columns' => [],
                    'worksheet_columns_related_fields' => [],
                ], false,
            ],
            [
                [
                    'worksheet_columns' => [],
                ], true,
            ],
            [
                [
                    'worksheet_columns' => [],
                    'worksheet_columns_related_fields' => '',
                ], true,
            ],
        ];
    }

    /**
     * @covers ::saveMobileWorksheetColumnConfig
     */
    public function testSaveMobileWorksheetColumnConfig()
    {
        $data = [
            [
                'name' => 'account_name',
            ],
            [
                'name' => 'campaign_name',
            ],
            [
                'name' => 'date_modified',
            ],
        ];

        $expectedData = [
            [
                'name' => 'account_name',
            ],
            [
                'name' => 'date_modified',
            ],
        ];

        $api = new \QuotesConfigApi();
        $api->saveMobileWorksheetColumnConfig([
            'worksheet_columns' => $data,
            'worksheet_columns_related_fields' => [],
        ]);

        $admin = \BeanFactory::newBean('Administration');
        $results = $admin->getConfigForModule('Quotes', 'mobile');

        $this->assertEquals($expectedData, $results['worksheet_columns']);
    }
}
