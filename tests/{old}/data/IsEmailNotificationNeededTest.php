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
 * Test if buisness logic works correct for determination whether notifications should be sent or not
 */
class isEmailNotificationNeededTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        SugarTestCallUtilities::removeCallUsers();
        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestHelper::tearDown();
    }

    /**
     * Test if isEmailNotificationNeeded works correctly
     *
     * @param $parameters - Different sets of parameters
     * @param $expectedResult - Expected result for isEmailNotificationNeeded with given parameters
     * @dataProvider dataProvider
     */
    public function testIsEmailNotificationNeeded($parameters, $expectedResult)
    {
        global $current_user;

        $call = new Call();
        $call->assigned_user_id = $parameters['assignedUserId'];
        $current_user->id = $parameters['currentUserId'];
        if ($parameters['isInstalling']) {
            $GLOBALS['installing'] = true;
        } else {
            $GLOBALS['installing'] = false;
        }
        $calendarEventsUtils = CalendarEventsUtils::getInstance();
        $calendarEventsUtils->setOldAssignedUserValue($parameters['assignedUserIdOld']);

        $result = $call->isEmailNotificationNeeded();

        $this->assertEquals($result, $expectedResult);
    }

    public static function dataProvider()
    {
        // $assignedUserIdOld = [7, 8];
        // $isInstalling = [false, true];
        // $assignedUserId = [8, 9];
        // $currentUserId = [9, 10];

        return [
            [
                [
                    'assignedUserIdOld' => 7,
                    'isInstalling' => false,
                    'assignedUserId' => 8,
                    'currentUserId' => 9,
                ],
                true,
            ],
            [
                [
                    'assignedUserIdOld' => 7,
                    'isInstalling' => false,
                    'assignedUserId' => 8,
                    'currentUserId' => 10,
                ],
                true,
            ],
            [
                [
                    'assignedUserIdOld' => 7,
                    'isInstalling' => false,
                    'assignedUserId' => 9,
                    'currentUserId' => 9,
                ],
                false,
            ],
            [
                [
                    'assignedUserIdOld' => 7,
                    'isInstalling' => false,
                    'assignedUserId' => 9,
                    'currentUserId' => 10,
                ],
                true,
            ],
            [
                [
                    'assignedUserIdOld' => 7,
                    'isInstalling' => true,
                    'assignedUserId' => 8,
                    'currentUserId' => 9,
                ],
                false,
            ],
            [
                [
                    'assignedUserIdOld' => 7,
                    'isInstalling' => true,
                    'assignedUserId' => 8,
                    'currentUserId' => 10,
                ],
                false,
            ],
            [
                [
                    'assignedUserIdOld' => 7,
                    'isInstalling' => true,
                    'assignedUserId' => 9,
                    'currentUserId' => 9,
                ],
                false,
            ],
            [
                [
                    'assignedUserIdOld' => 7,
                    'isInstalling' => true,
                    'assignedUserId' => 9,
                    'currentUserId' => 10,
                ],
                false,
            ],
            [
                [
                    'assignedUserIdOld' => 8,
                    'isInstalling' => false,
                    'assignedUserId' => 8,
                    'currentUserId' => 9,
                ],
                false,
            ],
            [
                [
                    'assignedUserIdOld' => 8,
                    'isInstalling' => false,
                    'assignedUserId' => 8,
                    'currentUserId' => 10,
                ],
                false,
            ],
            [
                [
                    'assignedUserIdOld' => 8,
                    'isInstalling' => false,
                    'assignedUserId' => 9,
                    'currentUserId' => 9,
                ],
                false,
            ],
            [
                [
                    'assignedUserIdOld' => 8,
                    'isInstalling' => false,
                    'assignedUserId' => 9,
                    'currentUserId' => 10,
                ],
                true,
            ],
            [
                [
                    'assignedUserIdOld' => 8,
                    'isInstalling' => true,
                    'assignedUserId' => 8,
                    'currentUserId' => 9,
                ],
                false,
            ],
            [
                [
                    'assignedUserIdOld' => 8,
                    'isInstalling' => true,
                    'assignedUserId' => 8,
                    'currentUserId' => 10,
                ],
                false,
            ],
            [
                [
                    'assignedUserIdOld' => 8,
                    'isInstalling' => true,
                    'assignedUserId' => 9,
                    'currentUserId' => 9,
                ],
                false,
            ],
            [
                [
                    'assignedUserIdOld' => 8,
                    'isInstalling' => true,
                    'assignedUserId' => 9,
                    'currentUserId' => 10,
                ],
                false,
            ],
        ];
    }
}
