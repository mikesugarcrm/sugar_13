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

require_once 'include/SugarEmailAddress/SugarEmailAddress.php';

class SugarEmailAddressAddChangeTest extends TestCase
{
    protected $email;
    protected $old_email = 'test@sugar.example.com';
    protected $old_uuid;

    /**
     * Fetch a SugarEmailAddress for retrieval/checking purposes
     * @param $uuid - uuid (guid) of row to read in
     * @return SugarEmailAddress
     */
    protected function readSugarEmailAddress($uuid)
    {
        $sea = new SugarEmailAddress();
        $sea->disable_row_level_security = true; // SugarEmailAddress doesn't roll with security
        $sea->retrieve($uuid);
        return $sea;
    }

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        $this->email = SugarTestSugarEmailAddressUtilities::createEmailAddress($this->old_email);
        $this->old_uuid = SugarTestSugarEmailAddressUtilities::fetchEmailIdByAddress($this->old_email);
    }

    protected function tearDown(): void
    {
        SugarTestSugarEmailAddressUtilities::removeCreatedContactAndRelationships();
        SugarTestSugarEmailAddressUtilities::removeAllCreatedEmailAddresses();
        SugarTestHelper::tearDown();
    }

    /**
     * @group bug57426
     */
    public function testEmailAddressesBrandNew()
    {
        $new_address = 'test_george@sugar.example.com';

        // now change the email, keeping track of bean UUIDs
        $old_uuid = $this->old_uuid;
        $uuid = $this->email->AddUpdateEmailAddress($new_address);

        $this->assertNotNull($uuid, 'Failed to enter the new email in the database!');
        $this->assertNotNull($old_uuid, 'Not seeing the old email in the database!');
        $this->assertNotEquals($uuid, $old_uuid, 'Same Email Address Bean used for different Email Addresses!');

        $new_sea = $this->readSugarEmailAddress($uuid);
        $old_sea = $this->readSugarEmailAddress($old_uuid);
        $this->assertNotNull($new_sea, 'New Email Address not saved in database!');
        $this->assertEquals($this->old_email, $old_sea->email_address, 'Old Email Address was improperly Changed');
        $this->assertEquals($new_address, $new_sea->email_address, 'New Email Address was improperly saved!');
    }

    public function testEmailAddressesNoChange()
    {
        $uuid = $this->email->AddUpdateEmailAddress($this->old_email);

        $this->assertNotNull($uuid, 'Where did my email address go?');
        $this->assertEquals($this->old_uuid, $uuid, 'We are using a different bean for the same email address!');

        $sea = $this->readSugarEmailAddress($uuid);
        $this->assertNotNull($sea, 'We lost our Email Address row!');
        $this->assertEquals($this->old_email, $sea->email_address, 'Our Email Address Improperly Changed!');
    }

    public function testEmailAddressesChangeCaps()
    {
        $new_address = 'TEST@SUGAR.example.COM';
        // change the email with caps
        $old_uuid = $this->old_uuid;
        $uuid = $this->email->AddUpdateEmailAddress($new_address);

        $this->assertNotNull($uuid, 'Failed to enter the new email in the database!');
        $this->assertNotNull($old_uuid, 'Not seeing the old email in the database!');
        $this->assertEquals($uuid, $old_uuid, 'Different Email Address Bean used for same Email Address!');

        $new_sea = $this->readSugarEmailAddress($uuid);
        $this->assertNotNull($new_sea, 'Email Address not found in DB!');
        $this->assertEquals($new_address, $new_sea->email_address, 'Email Address in DB was not updated.');
        $this->assertEquals(sugarStrToUpper($new_address), $new_sea->email_address_caps);
    }

    public function testEmailAddressUpdateWithId()
    {
        $new_address = 'T.E.S.T@sugar.example.com';
        $uuid = $this->email->AddUpdateEmailAddress($new_address, null, null, $this->old_uuid);

        $this->assertNotNull($uuid, 'Failed to enter the new email in the database!');
        $this->assertEquals($uuid, $this->old_uuid, 'Different Email Address Bean used despite passing an ID');

        $new_sea = $this->readSugarEmailAddress($uuid);
        $this->assertNotNull($new_sea, 'Email Address not found in DB!');
        $this->assertEquals($new_address, $new_sea->email_address, 'Email Address in DB was not updated.');
        $this->assertEquals(sugarStrToUpper($new_address), $new_sea->email_address_caps);
    }

    public function testEmailAddressUpdateInvalidOptOut()
    {
        //Set both to false
        $this->email->AddUpdateEmailAddress($this->old_email, false, false);
        $eab = $this->readSugarEmailAddress($this->old_uuid);
        $this->assertNotNull($eab->invalid_email);
        $this->assertEquals(0, $eab->invalid_email);
        $this->assertNotNull($eab->opt_out);
        $this->assertEquals(0, $eab->opt_out);

        //Set One but don't touch the other
        $this->email->AddUpdateEmailAddress($this->old_email, null, true);
        $eab = $this->readSugarEmailAddress($this->old_uuid);
        $this->assertNotNull($eab->invalid_email);
        $this->assertEquals(0, $eab->invalid_email);
        $this->assertEquals(1, $eab->opt_out);

        //Set the other and don't touch the first
        $this->email->AddUpdateEmailAddress($this->old_email, true, null);
        $eab = $this->readSugarEmailAddress($this->old_uuid);
        $this->assertEquals(1, $eab->invalid_email);
        $this->assertEquals(1, $eab->opt_out);
    }
}
