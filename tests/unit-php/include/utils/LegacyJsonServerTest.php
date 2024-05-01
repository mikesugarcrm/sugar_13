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

namespace Sugarcrm\SugarcrmTestsUnit\inc\utils;

use PHPUnit\Framework\TestCase;

/**
 * LegacyJsonServerTest tests
 * @coversDefaultClass \LegacyJsonServer
 */
class LegacyJsonServerTest extends TestCase
{
    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $GLOBALS['db'] = new \SugarTestDatabaseMock();
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        $GLOBALS['db'] = null;
    }

    /**
     * constructWhere tests
     * @covers ::constructWhere
     * @dataProvider dataProviderTestConstructWhere
     *
     * @param string $table name
     * @param string $module name
     * @param string $expectedResult
     */
    public function testConstructWhere($table, $module, $expectedResult)
    {
        $ljs = new \LegacyJsonServer();

        $conditions = [
            [
                'name' => 'full_name',
                'op' => 'starts_with',
                'value' => 'will',
            ], [
                'name' => 'email',
                'op' => 'starts_with',
                'value' => 'will',
            ], [
                'name' => 'account_name',
                'op' => 'starts_with',
                'value' => 'will',
            ],
        ];

        $queryObj = [
            'group' => 'or',
            'conditions' => $conditions,
        ];

        $actualResult = $ljs->constructWhere($queryObj, $table, $module);
        $this->assertStringEndsWith($expectedResult, $actualResult);
    }

    public function dataProviderTestConstructWhere()
    {
        return [
            [
                'contacts',
                'Contacts',
                ')',
            ], [
                'users',
                'Users',
                ") and users.status='Active'",
            ], [
                'leads',
                'Leads',
                ')',
            ],
        ];
    }
}
