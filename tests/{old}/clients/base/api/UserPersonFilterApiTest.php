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
 */
class UserPersonFilterApiTest extends TestCase
{
    public RestService $serviceMock;
    private PersonFilterApi $personFilterApi;
    public static array $users = [];

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');

        // Create test users
        for ($i = 0; $i < 10; $i++) {
            /** @var User $user */
            $user = BeanFactory::newBean('Users');
            $user->id = Sugarcrm\Sugarcrm\Util\Uuid::uuid1();
            $user->new_with_id = true;
            $user->status = 'Active';
            $user->name = "TEST $i User";
            $user->user_name = 'test_' . $i;
            $user->email1 = 'test_' . $i . '@email.com';
            $user->email2 = 'test2_' . $i . '@email.com';
            $user->save();
            self::$users[] = $user;
        }
    }

    protected function setUp(): void
    {
        $this->personFilterApi = new PersonFilterApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown(): void
    {
        SugarConfig::getInstance()->clearCache();
    }

    public static function tearDownAfterClass(): void
    {
        $userIds = "('" . implode("','", array_column(self::$users, 'id')) . "')";
        $GLOBALS['db']->query("DELETE FROM users WHERE id IN {$userIds}");
        SugarTestHelper::tearDown();
    }

    /**
     * @dataProvider providerMaxNumAndOffset
     */
    public function testMaxNumAndOffset(string $fields, string $orderBy, int $maxNum, $expected)
    {
        $reply = $this->personFilterApi->filterList(
            $this->serviceMock,
            [
                'module_list' => 'Users',
                'fields' => $fields,
                'max_num' => $maxNum,
                'offset' => 0,
                'order_by' => $orderBy,
            ]
        );
        $this->assertEquals($expected, $reply['next_offset'], 'Next offset is not set correctly');
    }

    public static function providerMaxNumAndOffset(): iterable
    {
        return [
            [
                'fields' => 'id,user_name',
                'order_by' => 'status:asc',
                'max_num' => 2,
                'expected' => 2,
            ],
            [
                'fields' => 'email,id,user_name',
                'order_by' => 'status:asc',
                'max_num' => 2,
                'expected' => 2,
            ], [
                'fields' => 'id,user_name',
                'order_by' => 'user_name:desc',
                'max_num' => 2,
                'expected' => 2,
            ], [
                'fields' => 'email,id,user_name',
                'order_by' => 'user_name:desc',
                'max_num' => 2,
                'expected' => 2,
            ], [
                'fields' => 'email,id,user_name',
                'order_by' => 'user_name:desc',
                'max_num' => 4,
                'expected' => 4,
            ],
        ];
    }
}
