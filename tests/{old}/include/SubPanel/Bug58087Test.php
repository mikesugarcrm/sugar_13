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

/**
 * Bug 58087 - Compose Email in activities sub panel
 *
 * Tests the presence of the notes module in subpanels for offline client. Extends
 * the SubPanelTestBase which handle most of the setup and tear down.
 */
class Bug58087Test extends SubPanelTestBase
{
    private $modListHeaderGlobal = [];
    private $sugarConfig;
    protected $testModule = 'Accounts';

    protected function setUp(): void
    {
        parent::setUp();

        // Set up our test defs - borrowed from Accounts subpaneldefs
        $this->testDefs = [
            'order' => 10,
            'sort_order' => 'desc',
            'sort_by' => 'date_start',
            'title_key' => 'LBL_ACTIVITIES_SUBPANEL_TITLE',
            'type' => 'collection',
            'subpanel_name' => 'activities',   //this values is not associated with a physical file.
            'header_definition_from_subpanel' => 'meetings',
            'module' => 'Activities',
            'top_buttons' => [
                ['widget_class' => 'SubPanelTopCreateTaskButton'],
                ['widget_class' => 'SubPanelTopScheduleMeetingButton'],
                ['widget_class' => 'SubPanelTopScheduleCallButton'],
                ['widget_class' => 'SubPanelTopComposeEmailButton'],
            ],
            'collection_list' => [
                'tasks' => [
                    'module' => 'Tasks',
                    'subpanel_name' => 'ForActivities',
                    'get_subpanel_data' => 'tasks',
                ],
                'meetings' => [
                    'module' => 'Meetings',
                    'subpanel_name' => 'ForActivities',
                    'get_subpanel_data' => 'meetings',
                ],
                'calls' => [
                    'module' => 'Calls',
                    'subpanel_name' => 'ForActivities',
                    'get_subpanel_data' => 'calls',
                ],
            ],
        ];

        // This test requires modListHeader
        if (!empty($GLOBALS['modListHeader'])) {
            $this->modListHeaderGlobal = $GLOBALS['modListHeader'];
        }

        $GLOBALS['modListHeader'] = query_module_access_list($GLOBALS['current_user']);

        // One test will modify sugar_config
        $this->sugarConfig = $GLOBALS['sugar_config'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (!empty($this->modListHeaderGlobal)) {
            $GLOBALS['modListHeader'] = $this->modListHeaderGlobal;
        }

        $GLOBALS['sugar_config'] = $this->sugarConfig;
    }

    /**
     * @group Bug58087
     */
    public function testEmailActionMenuItemExistsInSubpanelActionsOnDefaultInstallation()
    {
        $subpanel = new aSubPanel('activities', $this->testDefs, $this->testBean);
        $buttons = $subpanel->get_buttons();
        $test = $this->hasEmailAction($buttons);
        $this->assertTrue($test, 'Compose Email action missing when it was expected');
    }

    /**
     * Helper method that scans an array and checks for the presence of a value
     *
     * @param array $buttons
     * @return bool
     */
    private function hasEmailAction($buttons)
    {
        foreach ($buttons as $button) {
            if (isset($button['widget_class']) && $button['widget_class'] == 'SubPanelTopComposeEmailButton') {
                return true;
            }
        }

        return false;
    }
}
