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
use Sugarcrm\Sugarcrm\AccessControl\SugarFeatureVoter;
use Sugarcrm\Sugarcrm\Entitlements\Subscription;

/**
 * Class SugarFeatureVoterTest
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\AccessControl\SugarFeatureVoter
 */
class SugarFeatureVoterTest extends TestCase
{
    /**
     * @covers ::vote
     * @dataProvider voteProvider
     */
    public function testVote($deniedList, $entitled, $expected)
    {
        $voter = $this->getMockBuilder(SugarFeatureVoter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSystemCrmkeys', 'getDeniedList'])
            ->getMock();

        $voter->expects($this->any())
            ->method('getSystemCrmkeys')
            ->will($this->returnValue($entitled));

        $voter->expects($this->any())
            ->method('getDeniedList')
            ->will($this->returnValue($deniedList));

        $this->assertSame(
            $expected,
            $voter->vote(AccessControlManager::FEATURES_KEY, SugarFeatureVoter::MODULE_LOADER_UPLOAD_FEATURE_NAME, '')
        );
    }

    public function voteProvider()
    {
        return [
            'not in the denied key list' => [
                [Subscription::SUGAR_SELL_ESSENTIALS_KEY],
                [Subscription::SUGAR_SERVE_KEY => true],
                true,
            ],
            'single denied key list' => [
                [Subscription::SUGAR_SELL_ESSENTIALS_KEY],
                [
                    Subscription::SUGAR_SELL_ESSENTIALS_KEY => true,
                ],
                false,
            ],
            'multi denied keys' => [
                [Subscription::SUGAR_SELL_ESSENTIALS_KEY, Subscription::SUGAR_SERVE_KEY],
                [
                    Subscription::SUGAR_SERVE_KEY => true,
                ],
                false,
            ],
            'contains both denied key and non denied key' => [
                [Subscription::SUGAR_SELL_ESSENTIALS_KEY],
                [
                    Subscription::SUGAR_SELL_ESSENTIALS_KEY => true,
                    Subscription::SUGAR_SERVE_KEY => true,
                ],
                true,
            ],
            'denied list is empty, the feature is accessible' => [
                [],
                [
                    Subscription::SUGAR_SELL_ESSENTIALS_KEY => true,
                ],
                true,
            ],
        ];
    }
}
