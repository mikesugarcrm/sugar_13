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
 * @coversDefaultClass UsersApi
 */
class UsersApiTest extends TestCase
{
    protected $userBean = null;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');

        $this->userBean = BeanFactory::newBean('Users');
        $this->userBean->id = create_guid();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        unset($this->userBean);
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::createUser
     * @dataProvider providerTestCreateUser
     * @param array $args The array of arguments to createUser
     * @param bool $expectException Whether the API should throw an exception
     * @throws SugarApiExceptionInvalidParameter
     */
    public function testCreateUser($args, $expectException)
    {
        $mockApi = $this->getMockBuilder(UsersApi::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createRecord'])
            ->getMock();

        $mockService = $this->getMockBuilder('ServiceBase')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        if ($expectException) {
            $this->expectException(SugarApiExceptionMissingParameter::class);
        } else {
            $mockApi->expects($this->once())->method('createRecord');
        }

        $mockApi->createUser($mockService, $args);
    }

    public function providerTestCreateUser()
    {
        return [
            [
                [
                    'module' => 'Users',
                ],
                true,
            ],
            [
                [
                    'module' => 'Users',
                    'is_admin' => false,
                    'is_group' => false,
                    'portal_only' => false,
                ],
                true,
            ],
            [
                [
                    'module' => 'Users',
                    'is_admin' => false,
                    'is_group' => false,
                    'portal_only' => true,
                ],
                false,
            ],
            [
                [
                    'module' => 'Users',
                    'is_admin' => false,
                    'is_group' => true,
                    'portal_only' => false,
                ],
                false,
            ],
            [
                [
                    'module' => 'Users',
                    'is_admin' => true,
                    'is_group' => false,
                    'portal_only' => false,
                ],
                true,
            ],
        ];
    }

    /**
     * @covers ::updateUserPreferenceFields
     * @dataProvider providerTestUpdateUserPreferenceFields
     * @param $existingValue
     * @param $newValue
     * @param $expectUpdated
     */
    public function testUpdateUserPreferenceFields($existingValue, $newValue, $expectUpdated)
    {
        $args = [
            'appearance' => $newValue,
        ];

        $mockApi = $this->getMockBuilder(UsersApi::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUserPreferenceFieldsHelper'])
            ->getMock();

        $mockHelper = $this->getMockBuilder(UserPreferenceFieldsHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setPreferenceField'])
            ->getMock();
        $mockApi->expects($this->once())
            ->method('getUserPreferenceFieldsHelper')
            ->willReturn($mockHelper);

        if ($expectUpdated) {
            $mockHelper->expects($this->once())
                ->method('setPreferenceField')
                ->with($this->userBean, 'appearance', $newValue);
        } else {
            $mockHelper->expects($this->never())
                ->method('setPreferenceField');
        }

        $this->userBean->setPreference('appearance', $existingValue);
        SugarTestReflection::callProtectedMethod($mockApi, 'updateUserPreferenceFields', [$this->userBean, $args]);
    }

    /**
     * @return array[] test value sets for testUpdateUserPreferenceFields
     */
    public function providerTestUpdateUserPreferenceFields()
    {
        return [
            ['light', 'dark', true],
            ['light', 'light', false],
        ];
    }
}
