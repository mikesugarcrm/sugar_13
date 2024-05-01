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

namespace Sugarcrm\SugarcrmTestsUnit\inc\AccessControl;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\AccessControl\AccessControlManager;
use Sugarcrm\Sugarcrm\AccessControl\SugarJobVoter;
use Sugarcrm\Sugarcrm\Entitlements\Subscription;

/**
 * Class SugarJobVoterTest
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\AccessControl\SugarJobVoter
 */
class SugarJobVoterTest extends TestCase
{
    /**
     * @covers ::vote
     * @dataProvider voteProvider
     */
    public function testVote($protectedList, $entitled, $expected)
    {
        $voter = $this->getMockBuilder(SugarJobVoter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllSystemSubscriptionKeys', 'getProtectedList'])
            ->getMock();

        $voter->expects($this->any())
            ->method('getAllSystemSubscriptionKeys')
            ->will($this->returnValue($entitled));

        $voter->expects($this->any())
            ->method('getProtectedList')
            ->will($this->returnValue($protectedList));

        $this->assertSame(
            $expected,
            $voter->vote(AccessControlManager::JOBS_KEY, 'any name', '')
        );
    }

    public function voteProvider()
    {
        return [
            'in the protected list' => [
                ['any name' => [Subscription::SUGAR_HINT_KEY]],
                [Subscription::SUGAR_SERVE_KEY, Subscription::SUGAR_HINT_KEY],
                true,
            ],
            'in the protected list, no system key' => [
                ['any name' => [Subscription::SUGAR_HINT_KEY]],
                [Subscription::SUGAR_SERVE_KEY],
                false,
            ],
            'not in protected key list' => [
                ['no name' => [Subscription::SUGAR_HINT_KEY]],
                [Subscription::SUGAR_SERVE_KEY],
                true,
            ],
            'no protected keys' => [
                [],
                [Subscription::SUGAR_SERVE_KEY],
                true,
            ],
        ];
    }
}
