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
 *  RS175: Prepare PreviouslyUsedFilters Api.
 */
class RS175Test extends TestCase
{
    /**
     * @var PreviouslyUsedFiltersApi
     */
    protected $api;

    /**
     * @var RestService
     */
    protected static $rest;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, false]);
        self::$rest = SugarTestRestUtilities::getRestServiceMock();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestFilterUtilities::removeAllCreatedFilters();
        SugarTestHelper::tearDown();
    }

    protected function setUp(): void
    {
        $this->api = new PreviouslyUsedFiltersApi();
    }

    protected function tearDown(): void
    {
        $this->api->setUsed(self::$rest, ['module_name' => 'Accounts', 'filters' => []]);
    }

    public function testApi()
    {
        global $current_user;
        $result = $this->api->setUsed(
            self::$rest,
            ['module_name' => 'Accounts', 'filters' => []]
        );
        $this->assertEmpty($result);

        $filter1 = SugarTestFilterUtilities::createUserFilter(
            $current_user->id,
            'RS189Filter1',
            json_encode([['module' => 'Accounts', 'name' => 'RS189Name1']])
        );
        $result = $this->api->setUsed(
            self::$rest,
            ['module_name' => 'Accounts', 'filters' => [$filter1->id]]
        );
        $this->assertCount(1, $result);
        $result = array_shift($result);
        $this->assertEquals($filter1->id, $result['id']);

        $result = $this->api->getUsed(self::$rest, ['module_name' => 'Accounts']);
        $this->assertCount(1, $result);
        $result = array_shift($result);
        $this->assertEquals($filter1->id, $result['id']);


        $filter2 = SugarTestFilterUtilities::createUserFilter(
            $current_user->id,
            'RS189Filter2',
            json_encode([['module' => 'Accounts', 'name' => 'RS189Name2']])
        );
        $this->api->setUsed(
            self::$rest,
            ['module_name' => 'Accounts', 'filters' => [$filter1->id, $filter2->id]]
        );

        $result = $this->api->deleteUsed(
            self::$rest,
            ['module_name' => 'Accounts', 'record' => $filter1->id]
        );
        $this->assertCount(1, $result);

        $result = $this->api->deleteUsed(
            self::$rest,
            ['module_name' => 'Accounts']
        );
        $this->assertEmpty($result);
    }
}
