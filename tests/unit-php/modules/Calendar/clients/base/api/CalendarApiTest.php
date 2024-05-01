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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Calendar\clients\base\api;

require_once 'include/SugarCache/SugarCache.php';

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \CalendarApi
 */
class CalendarApiTest extends TestCase
{
    /**
     * Data provider for getCalendarModules
     *
     * @return array
     */
    public function getCalendarsApiParamProvider(): array
    {
        return [
            'noModules' => [
                [],
                ['modules' => []],
            ],
            'someModules' => [
                [
                    [
                        'calendar_module' => 'Calls',
                        'allow_create' => 1,
                    ],
                    [
                        'calendar_module' => 'Tasks',
                        'allow_create' => 1,
                    ],
                ],
                [
                    'modules' => [
                        'Calls' => [
                            'objName' => 'Call',
                        ],
                        'Tasks' => [
                            'objName' => 'Task',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $GLOBALS['objectList'] = [
            'Calls' => 'Call',
            'Tasks' => 'Task',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        unset($GLOBALS['objectList']);
    }

    /**
     * Test api returns the list of modules
     *
     * @covers ::getCalendarModules
     * @dataProvider getCalendarsApiParamProvider
     */
    public function testGetCalendarModules($input, $expected)
    {


        $apiService = $this->createMock(\ServiceBase::class);
        $apiClass = $this->createPartialMock(
            \CalendarApi::class,
            ['queryCalendarModules', 'hasAccess']
        );
        $apiClass->method('queryCalendarModules')->willReturn($input);
        $apiClass->method('hasAccess')->willReturn(true);

        $result = $apiClass->getCalendarModules($apiService, []);

        $this->assertEquals(json_encode($expected), json_encode($result));
    }
}
