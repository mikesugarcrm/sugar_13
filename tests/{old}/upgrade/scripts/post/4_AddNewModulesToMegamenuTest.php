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

require_once 'upgrade/scripts/post/4_AddNewModulesToMegamenu.php';

/**
 * Test for adding new modules to the megamenu on upgrade
 */
class SugarUpgradeAddNewModulesToMegamenuTest extends UpgradeTestCase
{
    /**
     * Mock tabs array to be used in the TabController mock
     * @var array
     */
    public $mockTabs = [
        '0' => [
            'Accounts' => 'Accounts',
            'Bugs' => 'Bugs',
            'Contacts' => 'Contacts',
        ],
        '1' => [
            'Products' => 'Products',
        ],
    ];

    /**
     * Tests the criteria builder
     *
     * @param array $def new module definition array
     * @dataProvider newModuleDefProvider
     */
    public function testBuildCheckCriteria($def)
    {
        $ug = new SugarUpgradeAddNewModulesToMegamenu($this->upgrader);

        // Setup the upgrader to have the correct versions for this test
        $this->setupUpgraderVersions($ug, $def['setupVersions']);

        // Run the check
        $check = $ug->buildCheckCriteria($def);

        // Check the return
        $this->assertEquals($def['expectCheck'], $check);
    }

    /**
     * Tests the addition routine
     *
     * @param array $def new module definition array
     * @dataProvider moduleDefDataProvider
     */
    public function testGetNewTabsList($def, $expect)
    {
        $ug = new SugarUpgradeAddNewModulesToMegamenu($this->upgrader);

        $actual = $ug->getNewTabsList($this->mockTabs, $def);

        $this->assertEquals($expect, $actual);
    }

    /**
     * Tests the log message getter
     *
     * @param array $def new module definition array
     * @dataProvider newModuleDefProvider
     */
    public function testGetMessageToLog($def)
    {
        // Set the upgrader
        $ug = new SugarUpgradeAddNewModulesToMegamenu($this->upgrader);

        // Get the log message
        $msg = $ug->getMessageToLog($def);

        // Test it
        $this->assertEquals($def['expectMsg'], $msg);
    }

    public function newModuleDefProvider(): array
    {
        return [
            [
                'def' => [
                    'name' => 'PMSE Modules',
                    'toFlavor' => 'ent',
                    'fromVersion' => ['7.6.0', '<'],
                    'modules' => [
                        'pmse_Project',
                        'pmse_Inbox',
                        'pmse_Business_Rules',
                        'pmse_Emails_Templates',
                    ],
                    'setupVersions' => [
                        'from_version' => '6.7.23',
                        'from_flavor' => 'corp',
                        'to_version' => '7.6.1',
                        'to_flavor' => 'ent',
                    ],
                    'expectCheck' => true,
                    'expectMsg' => 'Megamenu module list updated with PMSE Modules',
                ],
            ],
            [
                'def' => [
                    'name' => 'Tags Module',
                    'fromVersion' => ['7.7.0', '<'],
                    'modules' => [
                        'Tags',
                    ],
                    'setupVersions' => [
                        'from_version' => '7.6.1',
                        'from_flavor' => 'ent',
                        'to_version' => '7.7.0',
                        'to_flavor' => 'ent',
                    ],
                    'expectCheck' => true,
                    'expectMsg' => 'Megamenu module list updated with Tags Module',
                ],
            ],
            [
                'def' => [
                    'toFlavor' => 'ult',
                    'modules' => [
                        'Test1',
                    ],
                    'setupVersions' => [
                        'from_version' => '6.7.23',
                        'from_flavor' => 'ent',
                        'to_version' => '7.6.1',
                        'to_flavor' => 'ent',
                    ],
                    'expectCheck' => false,
                    'expectMsg' => 'Megamenu module list updated',
                ],
            ],
            [
                'def' => [
                    'fromVersion' => ['7.6.1', '<'],
                    'toVersion' => ['7.6.2', '>='],
                    'modules' => [
                        'Test2',
                    ],
                    'setupVersions' => [
                        'from_version' => '7.6.1',
                        'from_flavor' => 'ent',
                        'to_version' => '7.6.2',
                        'to_flavor' => 'ent',
                    ],
                    'expectCheck' => false,
                    'expectMsg' => 'Megamenu module list updated',
                ],
            ],
            [
                'def' => [
                    'fromVersion' => ['7.6.1', '<='],
                    'toVersion' => ['7.6.2', '>='],
                    'modules' => [
                        'Test2',
                    ],
                    'setupVersions' => [
                        'from_version' => '7.6.1',
                        'from_flavor' => 'ent',
                        'to_version' => '7.6.2',
                        'to_flavor' => 'ent',
                    ],
                    'expectCheck' => true,
                    'expectMsg' => 'Megamenu module list updated',
                ],
            ],
            // Tests flavor conversion on 7.7+
            [
                'def' => [
                    'name' => 'PMSE Modules Converted',
                    'fromFlavor' => 'pro',
                    'toFlavor' => ['ent', 'ult'],
                    'fromVersion' => ['7.7', '>='],
                    'modules' => [
                        'pmse_Project',
                        'pmse_Inbox',
                        'pmse_Business_Rules',
                        'pmse_Emails_Templates',
                    ],
                    'setupVersions' => [
                        'from_version' => '7.7.0.0',
                        'from_flavor' => 'pro',
                        'to_version' => '7.7.0.0',
                        'to_flavor' => 'ult',
                    ],
                    'expectCheck' => true,
                    'expectMsg' => 'Megamenu module list updated with PMSE Modules Converted',
                ],
            ],
            // Business Centers module added (pre-9.1.0 -> 9.1.0)
            [
                'def' => [
                    'name' => 'Business Centers Module',
                    'fromVersion' => ['9.1.0', '<'],
                    'toFlavor' => ['ent'],
                    'modules' => ['BusinessCenters'],
                    'setupVersions' => [
                        'from_version' => '9.0.0',
                        'from_flavor' => 'ent',
                        'to_version' => '9.1.0',
                        'to_flavor' => 'ent',
                    ],
                    'expectCheck' => true,
                    'expectMsg' => 'Megamenu module list updated with Business Centers Module',
                ],
            ],
            // Business Centers module added (9.1.0+ conversion)
            [
                'def' => [
                    'name' => 'Business Centers Module',
                    'fromVersion' => ['9.1.0', '>='],
                    'toFlavor' => ['ent'],
                    'modules' => ['BusinessCenters'],
                    'setupVersions' => [
                        'from_version' => '9.1.0',
                        'from_flavor' => 'pro',
                        'to_version' => '9.1.0',
                        'to_flavor' => 'ent',
                    ],
                    'expectCheck' => true,
                    'expectMsg' => 'Megamenu module list updated with Business Centers Module',
                ],
            ],
        ];
    }

    public function moduleDefDataProvider(): array
    {
        return [
            [
                'def' => [
                    'modules' => [
                        'Voldemort',
                        'Gandalf',
                        'Palpatine',
                    ],
                ],
                'expect' => [
                    '0' => [
                        'Accounts' => 'Accounts',
                        'Bugs' => 'Bugs',
                        'Contacts' => 'Contacts',
                        'Voldemort' => 'Voldemort',
                        'Gandalf' => 'Gandalf',
                        'Palpatine' => 'Palpatine',
                    ],
                    '1' => [
                        'Products' => 'Products',
                    ],
                ],
            ],
            [
                'def' => [
                    'modules' => [
                        'Accounts',
                        'Azog',
                        'Hammerhead',
                    ],
                ],
                'expect' => [
                    '0' => [
                        'Accounts' => 'Accounts',
                        'Bugs' => 'Bugs',
                        'Contacts' => 'Contacts',
                        'Azog' => 'Azog',
                        'Hammerhead' => 'Hammerhead',
                    ],
                    '1' => [
                        'Products' => 'Products',
                    ],
                ],
            ],
            [
                'def' => [
                    'modules' => [
                        'Accounts',
                        'Products',
                    ],
                    'forceVisible' => true,
                ],
                'expect' => [
                    '0' => [
                        'Accounts' => 'Accounts',
                        'Bugs' => 'Bugs',
                        'Contacts' => 'Contacts',
                        'Products' => 'Products',
                    ],
                    '1' => [],
                ],
            ],
        ];
    }

    public function setupUpgraderVersions($ug, $data)
    {
        foreach ($data as $k => $v) {
            $ug->upgrader->$k = $v;
        }
    }
}
