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
 * @coversDefaultClass StudioModule
 */
class StudioModuleTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $beanList = [];
        $beanFiles = [];
        require 'include/modules.php';
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
    }

    public static function tearDownAfterClass(): void
    {
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_list_strings']);
    }

    public function testGetViewsForCasesHasRecordDashlet()
    {
        $sm = new StudioModule('Cases');
        $views = $sm->getViews();
        $defs = array_values($views);
        $expectedDef = [
            'name' => 'LBL_RECORDDASHLETVIEW',
            'type' => 'recorddashletview',
            'image' => 'RecordDashletView',
        ];
        $this->assertEquals($expectedDef, $defs[2]);
    }

    /**
     * @covers ::getViews
     */
    public function testGetViewsForCallsHasPreview()
    {
        $sm = new StudioModule('Calls');
        $views = $sm->getViews();
        $defs = array_values($views);
        $expectedDef = [
            'name' => 'LBL_PREVIEWVIEW',
            'type' => 'previewview',
            'image' => 'PreviewView',
        ];
        $this->assertContains($expectedDef, $defs);
    }

    public function providerGetType()
    {
        return [
            ['Meetings', 'basic'],
            ['Calls', 'basic'],
            ['Accounts', 'company'],
            ['Contacts', 'person'],
            ['Leads', 'person'],
            ['Cases', 'issue'],
        ];
    }

    /**
     * @ticket 50977
     *
     * @dataProvider providerGetType
     */
    public function testGetTypeFunction($module, $type)
    {
        $SM = new StudioModule($module);
        $this->assertEquals($type, $SM->getType(), 'Failed asserting that module:' . $module . ' is of type:' . $type);
    }

    /**
     * @covers ::getModuleSubpanels
     * @dataProvider providerGetModuleSubpanels
     */
    public function testGetModuleSubpanels($defs, $sourceModule, $expected)
    {
        $stub = $this->getStudioModuleMock();

        $result = SugarTestReflection::callProtectedMethod(
            $stub,
            'getModuleSubpanels',
            [$defs, $sourceModule]
        );

        $this->assertEquals($result, $expected);
    }

    public function providerGetModuleSubpanels()
    {
        return [
            [
                [
                    'accounts_meetings_1' => ['module' => 'Accounts'],
                    'notes_meetings_1' => ['module' => 'Notes'],
                ],
                'Accounts',
                ['accounts_meetings_1'],
            ],
            [
                [
                    'notes' => ['module' => 'Notes'],
                ],
                'Accounts',
                [],
            ],
            [
                [],
                'Accounts',
                [],
            ],
        ];
    }

    /**
     * @return StudioModule
     */
    protected function getStudioModuleMock(array $methods = null)
    {
        return $this->getMockBuilder('StudioModule')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
