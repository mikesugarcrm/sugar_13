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
use Sugarcrm\Sugarcrm\AccessControl\SugarRecordVoter;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class SugarRecordVoterTest
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\AccessControl\SugarRecordVoter
 */
class SugarRecordVoterTest extends TestCase
{
    /**
     * @covers ::vote
     * @dataProvider voteProvider
     */
    public function testVote($accessConfig, $module, $id, $entitled, $expected)
    {
        $voter = $this->getMockBuilder(SugarRecordVoter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentUserSubscriptions', 'getProtectedList'])
            ->getMock();

        $voter->expects($this->any())
            ->method('getCurrentUserSubscriptions')
            ->will($this->returnValue($entitled));

        $voter->expects($this->any())
            ->method('getProtectedList')
            ->will($this->returnValue($accessConfig));

        $this->assertSame(
            $expected,
            $voter->vote(AccessControlManager::RECORDS_KEY, $module, $id)
        );
    }

    public function voteProvider()
    {
        return [
            [
                ['ACL_MODULE_NAME' => ['id_guid' => ['SUGAR_SERVE']]],
                'ACL_MODULE_NAME',
                'id_guid',
                ['SUGAR_SERVE'],
                true,
            ],
            [
                ['ACL_MODULE_NAME' => ['id_guid' => ['SUGAR_SERVE']]],
                'ACL_MODULE_NAME',
                'id_guid',
                ['SUGAR_SERVE', 'CURRENT'],
                true,
            ],
            [
                ['ACL_MODULE_NAME' => ['id_guid' => ['SUGAR_SERVE']]],
                'ACL_MODULE_NAME',
                'id_guid',
                ['NOT_SERVICE_CCLOUD'],
                false,
            ],
            [
                ['ACL_MODULE_NAME' => ['id_guid' => ['SUGAR_SERVE']]],
                'ACL_MODULE_NAME',
                null,
                ['NOT_SERVICE_CCLOUD'],
                true,
            ],
        ];
    }
}
