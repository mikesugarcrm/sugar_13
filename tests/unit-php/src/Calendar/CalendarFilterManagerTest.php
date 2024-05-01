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

namespace Sugarcrm\SugarcrmTestsUnit\Calendar;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Calendar\CalendarFilterManager;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Calendar\CalendarFilterManager
 */
class CalendarFilterManagerTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['bwcModules'] = [];
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['bwcModules']);
        \DBManagerFactory::disconnectAll();
    }

    public function getDefaultFiltersProvider(): array
    {
        return [
            [
                'module' => 'Calls',
            ],
            [
                'module' => 'Meetings',
            ],
        ];
    }

    /**
     * @covers ::getDefaultFilters
     * @dataProvider getDefaultFiltersProvider
     */
    public function testGetDefaultFilters($module): void
    {
        $filterManagerClass = $this->createPartialMock(
            CalendarFilterManager::class,
            []
        );

        $result = $filterManagerClass->getDefaultFilters($module);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('all_records', $result);
    }
}
