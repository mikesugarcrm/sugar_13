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
 * @coversDefaultClass Email
 */
class EmailDirectionTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        OutboundEmailConfigurationTestHelper::setUp();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass(): void
    {
        OutboundEmailConfigurationTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    protected function tearDown(): void
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        parent::tearDown();
    }

    public function directionProvider()
    {
        return [
            [
                Email::STATE_DRAFT,
                'getEmailAddressForUser',
                ['getEmailAddressForContact'],
                Email::DIRECTION_UNKNOWN,
            ],
            [
                Email::STATE_ARCHIVED,
                'getEmailAddressForUser',
                ['getEmailAddressForContact'],
                Email::DIRECTION_OUTBOUND,
            ],
            [
                Email::STATE_ARCHIVED,
                'getEmailAddressForContact',
                ['getEmailAddressForUser'],
                Email::DIRECTION_INBOUND,
            ],
            [
                Email::STATE_ARCHIVED,
                'getEmailAddressForUser',
                ['getEmailAddressForUser'],
                Email::DIRECTION_INTERNAL,
            ],
            [
                Email::STATE_ARCHIVED,
                'getEmptyEmailAddress',
                ['getEmailAddressForContact'],
                Email::DIRECTION_UNKNOWN,
            ],
            [
                Email::STATE_ARCHIVED,
                'getEmailAddressForUser',
                ['getEmailAddressForUser', 'getEmailAddressForContact', 'getEmailAddressForUser'],
                Email::DIRECTION_OUTBOUND,
            ],
        ];
    }

    /**
     * @dataProvider directionProvider
     * @covers ::save
     * @covers ::getDirection
     */
    public function testSave_SetsTheDirection(string $state, string $fromMethod, array $toMethods, string $direction)
    {
        $fromAddr = call_user_func([get_class($this), $fromMethod]);
        $toAddrs = [];

        foreach ($toMethods as $toMethod) {
            $toAddrs[] = call_user_func([get_class($this), $toMethod]);
        }

        $email = SugarTestEmailUtilities::createEmail(
            '',
            [
                'state' => $state,
                'from_addr' => $fromAddr,
                'to_addrs' => implode(',', $toAddrs),
            ]
        );

        $this->assertSame($direction, $email->direction);
    }

    protected static function getEmailAddressForUser()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        return $user->email1;
    }

    protected static function getEmailAddressForContact()
    {
        $contact = SugarTestContactUtilities::createContact();
        return $contact->email1;
    }

    protected static function getEmptyEmailAddress()
    {
        return '';
    }
}
