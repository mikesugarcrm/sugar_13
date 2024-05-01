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

use Exception;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\AccessControl\AccessControlManager;
use Sugarcrm\Sugarcrm\AccessControl\SugarVoter;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class SugarVoterTest
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\AccessControl\SugarVoter
 */
class SugarVoterTest extends TestCase
{
    /**
     * @covers ::getCurrentUserSubscriptions
     */
    public function testGetCurrentUserSubscriptionsException()
    {
        global $current_user;
        $current_user = null;
        $voter = new SugarVoter();

        $this->expectException(Exception::class);
        TestReflection::callProtectedMethod($voter, 'getCurrentUserSubscriptions', []);
    }

    /**
     * @covers ::supports
     */
    public function testSupports()
    {
        $voter = new SugarVoter();
        $this->assertSame(3, safeCount(TestReflection::getProtectedValue($voter, 'supportedKeys')));
        $this->assertFalse(TestReflection::callProtectedMethod($voter, 'supports', ['RECORDES']));
        $this->assertTrue(TestReflection::callProtectedMethod($voter, 'supports', ['WIDGETS']));
    }

    /**
     * @covers ::vote
     * @covers ::supports
     * @covers ::getSupportedKeys
     *
     * @dataProvider voteProvider
     */
    public function testVote($notAccessibleList, $module, $entitled, $expected)
    {
        $voter = $this->getMockBuilder(SugarVoter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentUserSubscriptions', 'getNotAccessibleModuleListByLicenseTypes'])
            ->getMock();

        $voter->expects($this->any())
            ->method('getCurrentUserSubscriptions')
            ->will($this->returnValue($entitled));

        $voter->expects($this->any())
            ->method('getNotAccessibleModuleListByLicenseTypes')
            ->will($this->returnValue($notAccessibleList));

        $this->assertSame(
            $expected,
            $voter->vote(AccessControlManager::MODULES_KEY, $module)
        );
    }

    public function voteProvider()
    {
        return [
            [
                [
                    'CampaignLog' => true,
                    'CampaignTrackers' => true,
                ],
                'BusinessCenters',
                ['SUGAR_SERVE'],
                true,
            ],
            [
                [
                    'CampaignLog' => true,
                    'CampaignTrackers' => true,
                ],
                'BusinessCenters',
                [],
                false,
            ],
            [
                [
                    'CampaignLog' => true,
                    'CampaignTrackers' => true,
                ],
                'BusinessCenters',
                ['NOT_SERVICE_CLOUD', 'SUGAR_SERVE'],
                true,
            ],
            [
                [
                    'CampaignLog' => true,
                    'CampaignTrackers' => true,
                ],
                'BusinessCenters',
                ['INVLIAD_SERVICE_CLOUD'],
                true,
            ],
        ];
    }

    /**
     * @covers ::vote
     * @covers ::supports
     * @covers ::getSupportedKeys
     *
     * @dataProvider voteForDashletsAndWidgetsProvider
     */
    public function testVoteForDashletsAndWidgets($protectedList, $subject, $entitled, $expected)
    {
        $voter = $this->getMockBuilder(SugarVoter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentUserSubscriptions', 'getProtectedList'])
            ->getMock();

        $voter->expects($this->any())
            ->method('getCurrentUserSubscriptions')
            ->will($this->returnValue($entitled));

        $voter->expects($this->any())
            ->method('getProtectedList')
            ->will($this->returnValue($protectedList));

        $this->assertSame(
            $expected,
            $voter->vote(AccessControlManager::DASHLETS_KEY, $subject)
        );
    }

    public function voteForDashletsAndWidgetsProvider()
    {
        return [
            [
                ['id_guid' => ['SUGAR_SERVE']],
                'id_guid',
                ['SUGAR_SERVE'],
                true,
            ],
            [
                ['id_guid' => ['SUGAR_SERVE']],
                'id_guid',
                ['SUGAR_SERVE', 'CURRENT'],
                true,
            ],
            [
                ['id_guid' => ['SUGAR_SERVE']],
                'id_anyother',
                ['SUGAR_SERVE', 'CURRENT'],
                true,
            ],
            [
                ['id_guid' => ['SUGAR_SERVE']],
                'id_guid',
                ['NOT_SERVICE_CCLOUD'],
                false,
            ],
            [
                ['id_guid' => ['SUGAR_SERVE']],
                '',
                ['NOT_SERVICE_CCLOUD'],
                true,
            ],
        ];
    }
}
