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
 * @ticket 32487
 */
class GetNamePlusEmailAddressesForComposeTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    public function testGetNamePlusEmailAddressesForCompose()
    {
        $account = SugarTestAccountUtilities::createAccount();

        $email = BeanFactory::newBean('Emails');
        $this->assertEquals(
            "{$account->name} <{$account->email1}>",
            $email->getNamePlusEmailAddressesForCompose('Accounts', [$account->id])
        );
    }

    public function testGetNamePlusEmailAddressesForComposeMultipleIds()
    {
        $account1 = SugarTestAccountUtilities::createAccount();
        $account2 = SugarTestAccountUtilities::createAccount();
        $account3 = SugarTestAccountUtilities::createAccount();

        $email = BeanFactory::newBean('Emails');
        $addressString = $email->getNamePlusEmailAddressesForCompose('Accounts', [$account1->id, $account2->id, $account3->id]);
        $this->assertStringContainsString("{$account1->name} <{$account1->email1}>", $addressString);
        $this->assertStringContainsString("{$account2->name} <{$account2->email1}>", $addressString);
        $this->assertStringContainsString("{$account3->name} <{$account3->email1}>", $addressString);
    }

    public function testGetNamePlusEmailAddressesForComposePersonModule()
    {
        $contact = SugarTestContactUtilities::createContact();

        $email = BeanFactory::newBean('Emails');
        $this->assertEquals(
            $GLOBALS['locale']->formatName($contact) . " <{$contact->email1}>",
            $email->getNamePlusEmailAddressesForCompose('Contacts', [$contact->id])
        );
    }

    public function testGetNamePlusEmailAddressesForComposeUser()
    {
        $user = SugarTestUserUtilities::createAnonymousUser(false);
        $user->email1 = 'foo@bar.com';
        $user->save();

        $email = BeanFactory::newBean('Emails');
        $this->assertEquals(
            $GLOBALS['locale']->formatName($user) . " <{$user->email1}>",
            $email->getNamePlusEmailAddressesForCompose('Users', [$user->id])
        );
    }
}
