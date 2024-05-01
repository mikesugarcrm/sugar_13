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
 * @coversDefaultClass EmailMan
 */
class EmailManTest extends TestCase
{
    protected function tearDown(): void
    {
        SugarTestCampaignUtilities::removeAllCreatedCampaigns();
        SugarTestCampaignUtilities::removeAllCreatedCampaignLogs();
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    public function dataProviderForHasEmailBeenSent()
    {
        return [
            [
                'a@b',
                '123',
                'targeted',
                'a@b',
                '123',
                true,
            ],
            [
                'foo@bar',
                '123',
                'targeted',
                'a@b',
                '123',
                false,
            ],
            [
                'a@b',
                '456',
                'targeted',
                'a@b',
                '123',
                false,
            ],
            [
                'a@b',
                '123',
                'viewed',
                'a@b',
                '123',
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForHasEmailBeenSent
     *
     * @param string $clogEmail
     * @param string $clogMarketingId
     * @param string $clogActivityType
     * @param string $checkEmail
     * @param string $checkMarketingId
     * @param bool $expected
     */
    public function testHasEmailBeenSent(
        $clogEmail,
        $clogMarketingId,
        $clogActivityType,
        $checkEmail,
        $checkMarketingId,
        $expected
    ) {

        $this->createCampaignLog($clogEmail, $clogMarketingId, $clogActivityType);

        $emailMan = new EmailMan();
        $args = [$checkEmail, $checkMarketingId];
        $result = SugarTestReflection::callProtectedMethod($emailMan, 'hasEmailBeenSent', $args);

        $this->assertSame($result, $expected, 'Unexpected result when checking whether email was previously sent');
    }

    /**
     * Create Campaign Log with specific fields pre-populated
     *
     * @param string $email
     * @param string $marketingId
     * @param string $activityType
     * @return null|SugarBean
     */
    private function createCampaignLog($email, $marketingId, $activityType)
    {
        $campaign = SugarTestCampaignUtilities::createCampaign();
        $relatedContact = SugarTestContactUtilities::createContact();

        $extraVars = [
            'more_information' => $email,
            'marketing_id' => $marketingId,
        ];

        SugarTestCampaignUtilities::createCampaignLog(
            $campaign->id,
            $activityType,
            $relatedContact,
            $extraVars
        );
    }
}
