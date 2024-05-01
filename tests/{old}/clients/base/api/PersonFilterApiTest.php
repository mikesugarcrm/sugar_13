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
 * @group ApiTests
 * @coversDefaultClass PersonFilterApi
 */
class PersonFilterApiTest extends TestCase
{
    /**
     * @var \PersonFilterApi|mixed
     */
    public $personFilterApi;
    public $personUnifiedSearchApi;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        $this->personFilterApi = new PersonFilterApi();
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::filterList
     * @dataProvider providerTestInactiveUserFlag
     * @param bool $filterInactiveUsers true if the inactive filter flag is set
     * @param bool $includeStatusFilter true if there is a status filter applied to the query
     */
    public function testInactiveUserFlag($filterInactiveUsers, $includeStatusFilter)
    {
        $GLOBALS['current_user']->status = 'Inactive';
        $GLOBALS['current_user']->save();
        $args = [
            'module_list' => 'Users',
            'filterInactive' => $filterInactiveUsers,
        ];
        if ($includeStatusFilter) {
            $args['filter'] = [['status' => ['$in' => ['Inactive']]]];
        }
        $list = $this->personFilterApi->filterList(new PersonFilterApiMockUp(), $args);
        $list = $list['records'];
        $expected = [];
        foreach ($list as $record) {
            $expected[] = $record['id'];
        }

        // Only filter inactive users if the filter flag is set and there is no
        // filter applied on the status field
        $this->assertEquals(
            $filterInactiveUsers && !$includeStatusFilter,
            !in_array($GLOBALS['current_user']->id, $expected)
        );
    }

    /**
     * Provider for testInactiveUserFlag
     *
     * @return string[][]
     */
    public function providerTestInactiveUserFlag()
    {
        return [
            [true, true],
            [true, false],
            [false, true],
            [false, false],
        ];
    }

    /**
     * Bug 61073
     *
     * @covers ::filterList
     * @dataProvider providerTestPortalUserFlag
     * @param bool $filterPortalUsers true if the portal filter flag is set
     */
    public function testPortalUserFlag($filterPortalUsers)
    {
        $GLOBALS['current_user']->portal_only = 1;
        $GLOBALS['current_user']->save();
        $args = [
            'module_list' => 'Users',
            'filterPortal' => $filterPortalUsers,
        ];
        $list = $this->personFilterApi->filterList(new PersonFilterApiMockUp(), $args);
        $list = $list['records'];
        $expected = [];
        foreach ($list as $record) {
            $expected[] = $record['id'];
        }

        // Only filter portal users if the filter flag is set
        $this->assertEquals($filterPortalUsers, !in_array($GLOBALS['current_user']->id, $expected));
    }

    /**
     * Provider for testPortalUserFlag
     *
     * @return string[][]
     */
    public function providerTestPortalUserFlag()
    {
        return [
            [true],
            [false],
        ];
    }

    public function testNoShowOnEmployees()
    {
        $GLOBALS['current_user']->show_on_employees = 0;
        $GLOBALS['current_user']->employee_status = 'Active';
        $GLOBALS['current_user']->save();
        $args = ['module_list' => 'Employees',];
        $list = $this->personFilterApi->globalSearch(new PersonFilterApiMockUp(), $args);
        $list = $list['records'];
        $expected = [];
        foreach ($list as $record) {
            $expected[] = $record['id'];
        }

        $this->assertTrue(!in_array($GLOBALS['current_user']->id, $expected));
    }

    public function testShowOnEmployees()
    {
        $GLOBALS['current_user']->show_on_employees = 1;
        $GLOBALS['current_user']->employee_status = 'Active';
        $GLOBALS['current_user']->save();
        $args = ['module_list' => 'Employees',];
        $list = $this->personFilterApi->filterList(new PersonFilterApiMockUp(), $args);
        $list = $list['records'];
        $expected = [];
        foreach ($list as $record) {
            $expected[] = $record['id'];
        }

        $this->assertContains($GLOBALS['current_user']->id, $expected);
    }
}

class PersonFilterApiMockUp extends RestService
{
    public function __construct()
    {
        $this->user = $GLOBALS['current_user'];
    }

    public function execute()
    {
    }

    protected function handleException(\Throwable $exception)
    {
    }
}
