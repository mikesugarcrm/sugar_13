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
 * RS-176: Prepare List Api
 */
class RS176Test extends TestCase
{
    /**
     * @var ListApi
     */
    protected $listApi;

    /**
     * @var RestService
     */
    protected $serviceMock;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, true]);

        $this->listApi = new ListApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();

        for ($i = 0; $i < 5; $i++) {
            SugarTestAccountUtilities::createAccount();
        }
    }

    protected function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    /**
     * Data provider for list module test.
     *
     * @return array
     */
    public function listModuleDataProvider()
    {
        return [
            [
                [
                    'module' => 'Accounts',
                ],
                'Accounts',
            ],
            [
                [
                    'module' => 'Accounts',
                    'max_num' => '3',
                ],
                'Accounts',
                3,
                true,
                true,
            ],
        ];
    }

    /**
     * Test asserts behavior of listModule
     *
     * @dataProvider listModuleDataProvider
     */
    public function testListModule($args, $moduleName, $count = null, $checkCreatedIds = false)
    {
        $result = $this->listApi->listModule($this->serviceMock, $args);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('records', $result);
        $this->assertArrayHasKey('next_offset', $result);
        $this->assertNotEmpty($result['records']);

        if (null !== $count) {
            $this->assertEquals($count, safeCount($result['records']), 'Returned too many results');
        }
        foreach ($result['records'] as $record) {
            $this->assertArrayHasKey('id', $record);
            $this->assertArrayHasKey('_module', $record);
            $this->assertEquals($moduleName, $record['_module']);
            if ($checkCreatedIds) {
                $this->assertTrue(in_array($record['id'], SugarTestAccountUtilities::getCreatedAccountIds()));
            }
        }
    }
}
