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

class ConfigureShortcutBarTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ViewConfigureshortcutbar::getQuickCreateModules
     */
    public function testGetQuickCreateModules_returnsCorrectNumberOfEnabledAndDisabledModules()
    {
        global $moduleList;
        $moduleList = ['Accounts', 'Contacts', 'Leads'];
        $accountsMetadata = [
            'visible' => true,
            'order' => 0,
        ];
        $contactsMetadata = [
            'visible' => false,
        ];
        $leadsMetadata = [
            'visible' => true,
            'order' => 1,
        ];

        $stub = $this->createPartialMock('ViewConfigureShortcutBarMock', ['getQuickCreateMetadata']);
        $stub->method('getQuickCreateMetadata')
            ->withConsecutive(['Accounts'], ['Contacts'], ['Leads'])
            ->willReturnOnConsecutiveCalls($accountsMetadata, $contactsMetadata, $leadsMetadata);

        $actual = $stub->getQuickCreateModules();

        $this->assertEquals(2, safeCount($actual['enabled']), 'Should have two enabled modules');
        $this->assertEquals(1, safeCount($actual['disabled']), 'Should have one disabled module');
    }

    /**
     * @covers ViewConfigureshortcutbar::getQuickCreateModules
     */
    public function testGetQuickCreateModules_returnsOrderForAllEnabledModules()
    {
        global $moduleList;
        $moduleList = ['Accounts', 'Contacts', 'Leads'];
        $accountsMetadata = [
            'visible' => true,
            'order' => 0,
        ];
        $contactsMetadata = [
            'visible' => false,
        ];
        $leadsMetadata = [
            'visible' => true,
            'order' => 1,
        ];

        $stub = $this->createPartialMock('ViewConfigureShortcutBarMock', ['getQuickCreateMetadata']);
        $stub->method('getQuickCreateMetadata')
            ->withConsecutive(['Accounts'], ['Contacts'], ['Leads'])
            ->willReturnOnConsecutiveCalls($accountsMetadata, $contactsMetadata, $leadsMetadata);

        $actual = $stub->getQuickCreateModules();

        $this->assertEquals(0, $actual['enabled']['Accounts']['order'], 'Accounts module should have order of 0');
        $this->assertEquals(1, $actual['enabled']['Leads']['order'], 'Leads module should have order of 1');
        $this->assertArrayNotHasKey('order', $actual['disabled']['Contacts'], 'Contacts module should have not have order');
    }

    /**
     * @covers ViewConfigureshortcutbar::saveChangesToQuickCreateMetadata
     */
    public function testSaveChangesToQuickCreateMetadata_setDisabledToEnabled_moduleIsEnabled()
    {
        $enabled = [
            'Accounts' => ['visible' => true, 'order' => 0,],
        ];
        $disabled = [
            'Contacts' => ['visible' => false,],
        ];
        $modulesToEnable = [
            'Accounts' => 0,
            'Contacts' => 1,
        ];

        $mock = $this->createPartialMock('ViewConfigureShortcutBarMock', ['setQuickCreateMetadata']);
        $mock->expects($this->once())
            ->method('setQuickCreateMetadata')
            ->with(
                [
                    'visible' => true,
                    'order' => 1,
                ],
                'Contacts'
            )
            ->will($this->returnValue(true));

        $success = $mock->saveChangesToQuickCreateMetadata($enabled, $disabled, $modulesToEnable);

        $this->assertTrue($success, 'Should be successful');
    }

    /**
     * @covers ViewConfigureshortcutbar::saveChangesToQuickCreateMetadata
     */
    public function testSaveChangesToQuickCreateMetadata_setEnabledToDisabled_moduleIsDisabled()
    {
        $enabled = [
            'Accounts' => ['visible' => true, 'order' => 0,],
        ];
        $disabled = [
            'Contacts' => ['visible' => false,],
        ];
        $modulesToEnable = [];

        $mock = $this->createPartialMock('ViewConfigureShortcutBarMock', ['setQuickCreateMetadata']);
        $mock->expects($this->once())
            ->method('setQuickCreateMetadata')
            ->with(
                [
                    'visible' => false,
                ],
                'Accounts'
            )
            ->will($this->returnValue(true));

        $success = $mock->saveChangesToQuickCreateMetadata($enabled, $disabled, $modulesToEnable);

        $this->assertTrue($success, 'Should be successful');
    }

    /**
     * @covers ViewConfigureshortcutbar::saveChangesToQuickCreateMetadata
     */
    public function testSaveChangesToQuickCreateMetadata_noChange_setQuickCreateMetadataShouldNotBeCalled()
    {
        $enabled = [
            'Accounts' => ['visible' => true, 'order' => 0,],
        ];
        $disabled = [
            'Contacts' => ['visible' => false,],
        ];
        $modulesToEnable = [
            'Accounts' => 0,
        ];

        $mock = $this->createPartialMock('ViewConfigureShortcutBarMock', ['setQuickCreateMetadata']);
        $mock->expects($this->never())
            ->method('setQuickCreateMetadata');

        $success = $mock->saveChangesToQuickCreateMetadata($enabled, $disabled, $modulesToEnable);

        $this->assertTrue($success, 'Should be successful');
    }

    /**
     * @covers ViewConfigureshortcutbar::saveChangesToQuickCreateMetadata
     */
    public function testSaveChangesToQuickCreateMetadata_changeOrder_returnsCorrectOrder()
    {
        $enabled = [
            'Accounts' => ['visible' => true, 'order' => 0,],
            'Contacts' => ['visible' => true, 'order' => 1,],
        ];
        $disabled = [];
        $modulesToEnable = [
            'Contacts' => 0,
            'Accounts' => 1,
        ];

        $mock = $this->createPartialMock('ViewConfigureShortcutBarMock', ['setQuickCreateMetadata']);
        $mock->method('setQuickCreateMetadata')
            ->withConsecutive(
                [
                    [
                        'visible' => true,
                        'order' => 1,
                    ],
                    'Accounts',
                ],
                [
                    [
                        'visible' => true,
                        'order' => 0,
                    ],
                    'Contacts',
                ]
            )->willReturn(true);

        $success = $mock->saveChangesToQuickCreateMetadata($enabled, $disabled, $modulesToEnable);

        $this->assertTrue($success, 'Should be successful');
    }

    /**
     * @covers ViewConfigureshortcutbar::saveChangesToQuickCreateMetadata
     */
    public function testSaveChangesToQuickCreateMetadata_failsWhileWritingMetadata_returnsFalse()
    {
        $enabled = [
            'Accounts' => ['visible' => true, 'order' => 0,],
        ];
        $disabled = [
            'Contacts' => ['visible' => false,],
        ];
        $modulesToEnable = [
            'Accounts' => 0,
            'Contacts' => 1,
        ];

        $mock = $this->createPartialMock('ViewConfigureShortcutBarMock', ['setQuickCreateMetadata']);
        $mock->expects($this->once())
            ->method('setQuickCreateMetadata')
            ->will($this->returnValue(false));

        $success = $mock->saveChangesToQuickCreateMetadata($enabled, $disabled, $modulesToEnable);

        $this->assertFalse($success, 'Should be successful');
    }

    /**
     * @covers ViewConfigureshortcutbar::sortEnabledModules
     */
    public function testSortEnabledModules_shouldSortBasedOnOrderAttribute()
    {
        $modules = [
            'Accounts' => ['order' => 2],
            'Contacts' => ['order' => 0],
            'Leads' => ['order' => 1],
        ];

        $quickcreate = new ViewConfigureShortcutBarMock();
        $actual = $quickcreate->sortEnabledModules($modules);

        $this->assertEquals(3, count($actual), 'Should have three modules.');

        $index = 0;
        foreach ($actual as $module => $data) {
            switch ($index) {
                case 0:
                    $this->assertEquals('Contacts', $module, 'Contacts module should be first');
                    break;
                case 1:
                    $this->assertEquals('Leads', $module, 'Leads module should be second');
                    break;
                case 2:
                    $this->assertEquals('Accounts', $module, 'Accounts module should be third');
                    break;
            }
            $index++;
        }
    }

    /**
     * Bug #57703
     * @ticket 57703
     * @group 57703
     * @covers ViewConfigureshortcutbar::filterAndFormatModuleList
     */
    public function testFilterAndFormatModuleList_ReturnsFilteredData()
    {
        $moduleList = [
            'PdfManager' => ['module' => 'PdfManager'],
            'Accounts' => ['module' => 'Accounts'],
        ];
        $obj = new ViewConfigureShortcutBarMock();
        $results = $obj->filterAndFormatModuleList($moduleList);

        $this->assertEquals(1, count($results), 'Should return one array');
        $this->assertEquals('Accounts', $results[0]['module'], 'Should filter out PdfManager module');
        $this->assertEquals('Accounts', $results[0]['label'], 'Should have label attribute');
    }
}

/**
 * Mock class
 */
class ViewConfigureShortcutBarMock extends ViewConfigureshortcutbar
{
    public function getQuickCreateModules()
    {
        return parent::getQuickCreateModules();
    }

    public function getQuickCreateMetadata($module)
    {
        return parent::getQuickCreateMetadata($module);
    }

    public function saveChangesToQuickCreateMetadata($enabled, $disabled, $modulesToEnable)
    {
        return parent::saveChangesToQuickCreateMetadata($enabled, $disabled, $modulesToEnable);
    }

    public function sortEnabledModules($modules)
    {
        return parent::sortEnabledModules($modules);
    }

    public function filterAndFormatModuleList($moduleList)
    {
        return parent::filterAndFormatModuleList($moduleList);
    }
}
