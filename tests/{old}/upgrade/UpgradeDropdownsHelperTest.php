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
 * @covers UpgradeDropdownsHelper
 */
class UpgradeDropdownsHelperTest extends TestCase
{
    /**
     * @covers UpgradeDropdownsHelper::getDropdowns
     */
    public function testGetDropdowns_ReturnsCoreDropDowns()
    {
        $mockHelper = $this->createPartialMock('UpgradeDropdownsHelper', ['getAppListStringsFromFile']);
        $mockHelper->expects($this->once())
            ->method('getAppListStringsFromFile')
            ->willReturn([
                'sales_stage_default_key' => 'Prospecting',
                'activity_dom' => [
                    'Call' => 'Call',
                    'Meeting' => 'Meeting',
                    'Task' => 'Task',
                    'Email' => 'Email',
                    'Note' => 'Note',
                ],
                'meeting_status_dom' => [
                    'Planned' => 'Planned',
                    'Held' => 'Held',
                    'Not Held' => 'Not Held',
                ],
            ]);

        $actual = $mockHelper->getDropdowns('include/language/en_us.lang.php');

        $this->assertArrayHasKey('activity_dom', $actual);
        $this->assertArrayHasKey('meeting_status_dom', $actual);
        $this->assertEquals('Task', $actual['activity_dom']['Task']);
    }

    public function getDropDownsRestrictedDropDownsAreIgnoredProvider()
    {
        return [
            ['eapm_list'],
            ['eapm_list_documents'],
            ['eapm_list_import'],
            ['extapi_meeting_password'],
            ['Elastic_boost_options'],
            ['commit_stage_dom'],
            ['commit_stage_custom_dom'],
            ['commit_stage_binary_dom'],
            ['forecasts_config_ranges_options_dom'],
            ['forecasts_timeperiod_types_dom'],
            ['forecasts_chart_options_group'],
            ['forecasts_config_worksheet_layout_forecast_by_options_dom'],
            ['forecasts_timeperiod_options_dom'],
            ['generic_timeperiod_options'],
            ['sweetspot_theme_options'],
        ];
    }

    /**
     * @covers       UpgradeDropdownsHelper::getDropdowns
     * @dataProvider getDropDownsRestrictedDropDownsAreIgnoredProvider
     * @param $dropdown
     */
    public function testGetDropdowns_RestrictedDropDownsAreIgnored($dropdown)
    {
        $mockHelper = $this->createPartialMock('UpgradeDropdownsHelper', ['getAppListStringsFromFile']);
        $mockHelper->expects($this->once())
            ->method('getAppListStringsFromFile')
            ->willReturn(
                [
                    $dropdown => [
                        'Foo' => 'foo',
                        'Bar' => 'bar',
                        'Biz' => 'biz',
                        'Baz' => 'baz',
                    ],
                ]
            );

        $actual = $mockHelper->getDropdowns('include/language/en_us.lang.php');

        $this->assertEmpty($actual);
    }

    /**
     * @covers UpgradeDropdownsHelper::getDropdowns
     */
    public function testGetDropdowns_FileDoesNotExist_ReturnsAnEmptyArray()
    {
        $helper = new UpgradeDropdownsHelper();
        $actual = $helper->getDropdowns('./foobar');

        $this->assertEmpty($actual);
    }

    /**
     * @covers UpgradeDropdownsHelper::getDropdowns
     */
    public function testGetDropdowns_GLOBALSIsUsedInTheCustomizations_ReturnsCustomDropDowns()
    {
        $custom = <<<EOF
\$GLOBALS['app_list_strings']['activity_dom'] = array(
    'Call' => 'Call',
    'Meeting' => 'Meeting',
    'Task' => 'To Do',
    'Email' => 'Email',
    'Note' => 'Note',
    'SMS' => 'Text Message',
);

EOF;

        $tmpFileName = time();
        file_put_contents($tmpFileName, "<?php\n{$custom}\n");

        $helper = new UpgradeDropdownsHelper();
        $actual = $helper->getDropdowns($tmpFileName);

        $this->assertArrayHasKey('activity_dom', $actual);
        $this->assertEquals('To Do', $actual['activity_dom']['Task']);

        unlink($tmpFileName);
    }
}
