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
 * Test cases for php file Emails/emailAddress.php
 */
class EmailAddressTest extends TestCase
{
    private $emailaddress;
    private $testEmailAddressString = 'unitTest@sugarcrm.com';

    protected function setUp(): void
    {
        $this->emailaddress = new EmailAddress();
    }

    protected function tearDown(): void
    {
        unset($this->emailaddress);
        $query = "delete from email_addresses where email_address = '" . $this->testEmailAddressString . "'";
        $GLOBALS['db']->query($query);
    }

    public function testEmailAddress()
    {
        $id = create_guid();
        $module = '';
        $new_addrs = [];
        $primary = '';
        $replyTo = '';
        $invalid = '';
        $optOut = '';
        $in_workflow = false;
        $_REQUEST['_email_widget_id'] = 0;
        $_REQUEST['0emailAddress0'] = $this->testEmailAddressString;
        $_REQUEST['emailAddressPrimaryFlag'] = '0emailAddress0';
        $_REQUEST['emailAddressVerifiedFlag0'] = 'true';
        $_REQUEST['emailAddressVerifiedValue0'] = 'unitTest@sugarcrm.com';
        $requestVariablesSet = ['0emailAddress0', 'emailAddressPrimaryFlag', 'emailAddressVerifiedFlag0', 'emailAddressVerifiedValue0'];
        $this->emailaddress->save($id, $module, $new_addrs, $primary, $replyTo, $invalid, $optOut, $in_workflow);
        foreach ($requestVariablesSet as $k) {
            unset($_REQUEST[$k]);
        }

        $this->assertEquals($this->emailaddress->addresses[0]['email_address'], $this->testEmailAddressString);
        $this->assertEquals($this->emailaddress->addresses[0]['primary_address'], 1);
    }

    public function testSaveEmailAddressUsingSugarbeanSave()
    {
        $this->emailaddress->email_address = $this->testEmailAddressString;
        $this->emailaddress->opt_out = '1';
        $this->emailaddress->save();

        $this->assertTrue(!empty($this->emailaddress->id));
        $this->assertEquals(
            $this->emailaddress->id,
            $GLOBALS['db']->getOne("SELECT id FROM email_addresses WHERE id = '{$this->emailaddress->id}' AND email_address = '{$this->testEmailAddressString}' and opt_out = '1'"),
            'Email Address record not added'
        );
    }

    public function getEmails()
    {
        return [
            ['test@sugarcrm.com', '', 'test@sugarcrm.com'],
            ['John Doe <test@sugarcrm.com>', 'John Doe', 'test@sugarcrm.com'],
            ['"John Doe" <test@sugarcrm.com>', 'John Doe', 'test@sugarcrm.com'],
            ['"John Doe" <test@sugarcrm.com>', 'John Doe', 'test@sugarcrm.com'],
            ['"John Doe (<doe>)" <test@sugarcrm.com>', 'John Doe (doe)', 'test@sugarcrm.com'],
            ['Test <test@test>', 'Test', 'test@test'],
            // bad ones
            ['"John Doe (<doe>)"', 'John Doe (doe)', ''],
            ['John Doe <vlha>', 'John Doe vlha', ''],
            ['<script>alert(1)</script>', 'scriptalert(1)/script', ''],
        ];
    }

    /**
     * @dataProvider getEmails
     * @param string $addr
     * @param string $name
     * @param string $email
     */

    public function testSplitEmail($addr, $name, $email)
    {
        $parts = $this->emailaddress->splitEmailAddress($addr);
        $this->assertEquals($name, $parts['name']);
        $this->assertEquals($email, $parts['email']);
    }
}
