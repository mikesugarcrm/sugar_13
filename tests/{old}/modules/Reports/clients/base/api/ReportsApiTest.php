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
 * Test ReportsApi Api
 */
class ReportsApiTest extends TestCase
{
    /** @var RestService */
    protected $service = null;

    /** @var ReportsApi */
    protected $api = null;

    /** @var string */
    protected $reportPanelId = '';

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, true]);
        SugarTestHelper::setUp('app_list_strings');

        $reportPanel = SugarTestReportUtilities::createReportPanelForUser($GLOBALS['current_user']->id, 'reportId');
        $this->reportPanelId = $reportPanel['id'];

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new ReportsApi();
    }

    protected function tearDown(): void
    {
        SugarTestReportUtilities::removeReportPanel($this->reportPanelId);

        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts that retrievePanel returns data
     */
    public function testRetrievePanel()
    {
        $args = [
            'record' => 'reportId',
        ];
        $actual = $this->api->retrievePanel($this->service, $args);
        $this->assertNotEmpty($actual);
    }

    /**
     * Test asserts that savePanel saves the new configuration of the panel
     */
    public function testSavePanel()
    {
        $args = [
            'record' => 'reportId',
            'panels' => [
                [
                    'layout' => [
                        'type' => 'report-chart',
                        'label' => 'CHART',
                    ],
                    'width' => 5,
                    'height' => 10,
                    'x' => 0,
                    'y' => 0,
                ],
                [
                    'layout' => [
                        'type' => 'report-table',
                        'label' => 'LIST',
                    ],
                    'width' => 5,
                    'height' => 10,
                    'x' => 5,
                    'y' => 0,
                ],
            ],
        ];
        $actual = $this->api->savePanel($this->service, $args);
        $this->assertEquals(2, safeCount($actual['panels']));
    }
}
