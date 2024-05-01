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
 * @ticket 56938
 */
class Bug56938Test extends TestCase
{
    /** @var User */
    private $user;
    private $duplicate;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        SugarTestEmailAddressUtilities::addAddressToPerson(
            $this->user,
            'bug-56938-test@example.com'
        );
    }

    /**
     * Ensure that a new instance of EmailAddress is created during creating
     * User duplicate
     */
    public function testCreateDuplicate()
    {
        // retrieve created user from database in order to populate email addresses
        $original = new User();
        $original->retrieve($this->user->id);

        // simulate request parameters of "Duplicate" web form
        // Attempt to reuse the same email_address_id, but change email_address.
        $address = $original->emailAddress->addresses[0];
        $address['email_address'] = 'bug-56938-changed@example.com';
        $_REQUEST = [
            'Users_email_widget_id' => '1',
            'Users1emailAddress0' => $address['email_address'],
            'Users1emailAddressId0' => $address['email_address_id'],
        ];

        // create a duplicate and retrieve it from database as well
        $duplicate = $this->duplicate = new User();
        $duplicate->save();

        $retrieved = new User();
        $retrieved->retrieve($duplicate->id);

        // ensure that email address is created in duplicate
        $this->assertEquals(1, safeCount($retrieved->emailAddress->addresses));

        // ensure that the duplicate user's email address is correct
        $this->assertEquals($address['email_address'], $retrieved->emailAddress->addresses[0]['email_address']);

        // ensure that new instance of EmailAddress is created instead of
        // sharing the same instance between users
        $this->assertNotEquals(
            $original->emailAddress->addresses[0]['email_address_id'],
            $retrieved->emailAddress->addresses[0]['email_address_id']
        );

        // Ensure that the original user's email address did not change.
        $addresses = $original->emailAddress->getAddressesForBean($original, true);
        $this->assertEquals($original->emailAddress->addresses[0]['email_address'], $addresses[0]['email_address']);
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $_REQUEST = [];
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        if ($this->duplicate) {
            $this->duplicate->mark_deleted($this->duplicate->id);
        }
    }
}
