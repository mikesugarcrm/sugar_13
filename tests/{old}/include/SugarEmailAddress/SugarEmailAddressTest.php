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

use Sugarcrm\Sugarcrm\Util\Uuid;
use PHPUnit\Framework\TestCase;

require_once 'include/SugarEmailAddress/SugarEmailAddress.php';

/**
 * @coversDefaultClass SugarEmailAddress
 */
class SugarEmailAddressTest extends TestCase
{
    /** @var SugarEmailAddress */
    private $ea;

    private $primary1 = [
        'primary_address' => true,
        'email_address' => 'p1@example.com',
        'opt_out' => true,
        'invalid_email' => true,
    ];

    private $primary2 = [
        'primary_address' => true,
        'email_address' => 'p2@example.com',
        'opt_out' => false,
        'invalid_email' => false,
    ];

    private $alternate1 = [
        'primary_address' => false,
        'email_address' => 'a1@example.com',
        'opt_out' => false,
        'invalid_email' => false,
    ];

    private $alternate2 = [
        'primary_address' => false,
        'email_address' => 'a2@example.com',
        'opt_out' => false,
        'invalid_email' => false,
    ];

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    protected function setUp(): void
    {
        $this->ea = BeanFactory::newBean('EmailAddresses');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        SugarTestHelper::tearDown();
    }

    public function isValidEmailProvider()
    {
        return [
            ['john@john.com', true],
            ['----!john.com', false],
            ['john', false],
            // bugs: SI40068, SI44338
            ['jo&hn@john.com', true],
            ['joh#n@john.com.br', true],
            ['&#john@john.com', true],
            // bugs: SI40068, SI39186
            // note: a dot at the beginning or end of the local part are not allowed by RFC2822
            ['atendimento-hd.@uol.com.br', false],
            // bugs: SI13765
            ['st.-annen-stift@t-online.de', true],
            // bugs: SI39186
            ['qfflats-@uol.com.br', true],
            // bugs: SI44338
            ['atendimento-hd.?uol.com.br', false],
            ['atendimento-hd.?uol.com.br;aaa@com.it', false],
            ['f.grande@pokerspa.it', true],
            ['fabio.grande@softwareontheroad.it', true],
            ['fabio$grande@softwareontheroad.it', true],
            // bugs: SI44473
            // note: with MAR-1894 the infinite loop bug is no longer a problem, so this email address can pass
            // validation
            ['ettingshallprimaryschool@wolverhampton.gov.u', true],
            // bugs: SI13018
            ['Ert.F.Suu.-PA@pumpaudio.com', true],
            // bugs: SI23202
            ['test--user@example.com', true],
            // bugs: SI42403
            ['test@t--est.com', true],
            // bugs: SI42404
            ['t.-est@test.com', true],
            // bugs: MAR-1894
            ["o'hara@email.com", true],
            ['用户@例子.广告', true],
        ];
    }

    /**
     * @covers ::addAddress
     */
    public function testAddressesAreZeroBased()
    {
        // make sure that initially there are no addresses
        $this->assertCount(0, $this->ea->addresses);

        $this->ea->addAddress('test@example.com');
        $this->ea->addAddress('test@example.com');

        // make sure duplicate address is replaced
        $this->assertCount(1, $this->ea->addresses);
        $this->assertEquals(0, array_key_first($this->ea->addresses), 'Email addresses is not a 0-based array');
    }

    /**
     * @covers ::addAddress
     * @covers ::isValidEmail
     * @covers ::boolVal
     * @covers ::getEmailGUID
     */
    public function testAddresses_WithDifferentCapitalizationAreSeenAsDuplicates()
    {
        // make sure that initially there are no addresses
        $this->assertCount(0, $this->ea->addresses);

        $this->ea->addAddress('test@example.com');
        $this->ea->addAddress('Test@EXAMPLE.com');

        // make sure duplicate address is replaced
        $this->assertCount(1, $this->ea->addresses);
        $this->assertSame('Test@EXAMPLE.com', $this->ea->addresses[0]['email_address']);
    }

    /**
     * @covers ::handleLegacySave
     */
    public function testEmail1SavesWhenEmailIsEmpty()
    {
        $bean = BeanFactory::newBean('Accounts');
        $bean->email1 = 'a@a.com';
        $this->ea->handleLegacySave($bean);

        // Begin assertions
        $this->assertNotEmpty($this->ea->addresses);
        $this->assertArrayHasKey(0, $this->ea->addresses);
        $this->assertArrayHasKey('email_address', $this->ea->addresses[0]);
        $this->assertEquals('a@a.com', $this->ea->addresses[0]['email_address']);
    }

    /**
     * @covers ::handleLegacySave
     */
    public function testSavedEmailsPersistAfterSave()
    {
        $addresses = [
            ['email_address' => 'a@a.com', 'primary_address' => true],
            ['email_address' => 'b@b.com'],
            ['email_address' => 'c@c.com'],
            ['email_address' => 'd@d.com'],
        ];
        $bean = BeanFactory::newBean('Accounts');
        $bean->email = $addresses;
        $this->ea->handleLegacySave($bean);

        // Begin assertions
        $this->assertNotEmpty($this->ea->addresses);
        $this->assertEquals(4, safeCount($this->ea->addresses));
        $this->assertArrayHasKey(0, $this->ea->addresses);
        $this->assertArrayHasKey('email_address', $this->ea->addresses[0]);
        $this->assertEquals('a@a.com', $this->ea->addresses[0]['email_address']);
        $this->assertArrayHasKey(3, $this->ea->addresses);
        $this->assertArrayHasKey('email_address', $this->ea->addresses[3]);
        $this->assertEquals('d@d.com', $this->ea->addresses[3]['email_address']);
    }

    /**
     * @covers ::handleLegacySave
     */
    public function testSaveUsesCorrectValues()
    {
        // Set values on the email address object for testing
        $test = [
            [
                'email_address' => 'a@a.com',
                'email_address_id' => null,
                'primary_address' => true,
                'invalid_email' => false,
                'opt_out' => false,
                'reply_to_address' => false,
            ],
            [
                'email_address' => 'b@b.com',
                'email_address_id' => null,
                'primary_address' => false,
                'invalid_email' => false,
                'opt_out' => false,
                'reply_to_address' => false,
            ],
            [
                'email_address' => 'c@c.com',
                'email_address_id' => null,
                'primary_address' => false,
                'invalid_email' => false,
                'opt_out' => false,
                'reply_to_address' => false,
            ],
            [
                'email_address' => 'd@d.com',
                'email_address_id' => null,
                'primary_address' => false,
                'invalid_email' => false,
                'opt_out' => false,
                'reply_to_address' => false,
            ],
        ];

        $expect = [
            [
                'email_address' => 'z@z.com',
                'primary_address' => true,
                'reply_to_address' => false,
                'invalid_email' => false,
                'opt_out' => false,
            ],
        ];

        // Setup the test case
        $this->ea->fetchedAddresses = $this->ea->addresses = $test;
        $bean = BeanFactory::newBean('Contacts');
        $bean->email = $test;
        $bean->email1 = 'z@z.com';
        $bean->email2 = '';
        $this->ea->handleLegacySave($bean);

        // Expectation is that email1 will win
        foreach ($expect[0] as $key => $value) {
            $this->assertEquals($value, $this->ea->addresses[0][$key]);
        }
    }

    /**
     * @covers ::isValidEmail
     * @dataProvider isValidEmailProvider
     * @group bug40068
     */
    public function testIsValidEmail($email, $expected)
    {
        $this->assertEquals($expected, SugarEmailAddress::isValidEmail($email));
    }

    /**
     * When primary address exists, it's used to populate email1 property
     *
     * @covers ::populateLegacyFields
     */
    public function testPrimaryAttributeConsidered()
    {
        $bean = new SugarBean();
        $this->ea->addresses = [
            $this->alternate1,
            $this->primary1,
        ];

        $this->ea->populateLegacyFields($bean);

        $this->assertEquals('p1@example.com', $bean->email1);
        $this->assertEquals(true, $bean->email_opt_out);
        $this->assertEquals(true, $bean->invalid_email);
        $this->assertEquals('a1@example.com', $bean->email2);
    }

    /**
     * When multiple primary addresses exist, the first of them is used to
     * populate email1 property
     *
     * @covers ::populateLegacyFields
     */
    public function testMultiplePrimaryAddresses()
    {
        $bean = new SugarBean();
        $this->ea->addresses = [
            $this->primary1,
            $this->primary2,
        ];

        $this->ea->populateLegacyFields($bean);

        $this->assertEquals('p1@example.com', $bean->email1);
        $this->assertEquals('p2@example.com', $bean->email2);
    }

    /**
     * When no primary address exists, the first of non-primary ones is used to
     * populate email1 property
     *
     * @covers ::populateLegacyFields
     */
    public function testNoPrimaryAddress()
    {
        $bean = new SugarBean();
        $this->ea->addresses = [
            $this->alternate1,
            $this->alternate2,
        ];

        $this->ea->populateLegacyFields($bean);

        $this->assertEquals('a1@example.com', $bean->email1);
        $this->assertEquals('a2@example.com', $bean->email2);
    }

    /**
     * All available addresses are used to populate email properties
     *
     * @covers ::populateLegacyFields
     */
    public function testAllPropertiesArePopulated()
    {
        $bean = new SugarBean();
        $this->ea->addresses = [
            $this->primary1,
            $this->primary2,
            $this->alternate1,
            $this->alternate2,
        ];

        $this->ea->populateLegacyFields($bean);

        $this->assertEquals('p1@example.com', $bean->email1);
        $this->assertEquals('p2@example.com', $bean->email2);
        $this->assertEquals('a1@example.com', $bean->email3);
        $this->assertEquals('a2@example.com', $bean->email4);
    }

    /**
     * @covers ::getGuid
     */
    public function testGetGuid_EmailAddressExists()
    {
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $actual = $this->ea->getGuid($address->email_address);
        $this->assertSame($address->id, $actual);
    }

    /**
     * @covers ::getGuid
     */
    public function testGetGuid_EmailAddressDoesNotExist()
    {
        $actual = $this->ea->getGuid('address-' . Uuid::uuid1() . '@example.com');
        $this->assertSame('', $actual);
    }

    /**
     * @covers ::getEmailGUID
     * @covers ::getGuid
     */
    public function testGetEmailGUID_CreatesNewEmailAddress()
    {
        $guid = $this->ea->getEmailGUID('address-' . Uuid::uuid1() . '@example.com');
        SugarTestEmailAddressUtilities::setCreatedEmailAddress($guid);
        $this->assertNotEmpty($guid);
    }

    /**
     * @covers ::getEmailGUID
     * @covers ::getGuid
     */
    public function testGetEmailGUID_ReturnsExistingId()
    {
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $guid = $this->ea->getEmailGUID($address->email_address);
        $this->assertSame($address->id, $guid);
    }

    /**
     * @covers ::getEmailsQuery
     */
    public function testGetEmailsQuery()
    {
        $table = 'email_addresses';
        $q = $this->ea->getEmailsQuery('Contacts');
        $this->assertTrue($q->select->checkField('id', $table), 'id should be selected');
        $this->assertTrue($q->select->checkField('email_address', $table), 'email_address should be selected');
        $this->assertTrue($q->select->checkField('opt_out', $table), 'opt_out should be selected');
        $this->assertTrue($q->select->checkField('invalid_email', $table), 'invalid_email should be selected');
        //Note: Not sure how to test that the fields from the join are added to the select clause.
    }

    /**
     * @covers SugarEmailAddress::populateAddresses
     * @covers SugarEmailAddress::addAddress
     * @covers SugarEmailAddress::getEmailGUID
     * @covers SugarEmailAddress::getGuid
     */
    public function testPopulateAddresses_CreatesNewEmailAddress()
    {
        $address1 = Uuid::uuid4() . '@example.com';
        $ea = SugarTestEmailAddressUtilities::createEmailAddress($address1);

        $bean = BeanFactory::newBean('Contacts');
        $bean->emailAddress->addAddress($address1);

        $this->assertCount(
            1,
            $bean->emailAddress->addresses,
            'The bean should have one email address'
        );

        $address2 = Uuid::uuid4() . '@example.com';

        // Change the bean's email address.
        // The ID and email address are not in sync. The address is different. The ID is still passed but it is ignored.
        $_POST['Contacts_email_widget_id'] = $_REQUEST['Contacts_email_widget_id'] = 0;
        $_POST['emailAddressWidget'] = $_REQUEST['emailAddressWidget'] = 1;
        $_POST['useEmailWidget'] = $_REQUEST['useEmailWidget'] = true;
        $_POST['Contacts0emailAddress0'] = $_REQUEST['Contacts0emailAddress0'] = $address2;
        $_POST['Contacts0emailAddressId0'] = $_REQUEST['Contacts0emailAddressId0'] = $ea->id;
        $_POST['Contacts0emailAddressVerifiedFlag0'] = $_REQUEST['Contacts0emailAddressVerifiedFlag0'] = true;
        $_POST['Contacts0emailAddressVerifiedValue0'] = $_REQUEST['Contacts0emailAddressVerifiedValue0'] = $address2;

        $bean->emailAddress->populateAddresses($bean->id, $bean->module_name);

        unset($_POST['Contacts_email_widget_id']);
        unset($_REQUEST['Contacts_email_widget_id']);
        unset($_POST['emailAddressWidget']);
        unset($_REQUEST['emailAddressWidget']);
        unset($_POST['useEmailWidget']);
        unset($_REQUEST['useEmailWidget']);
        unset($_POST['Contacts0emailAddress0']);
        unset($_REQUEST['Contacts0emailAddress0']);
        unset($_POST['Contacts0emailAddressId0']);
        unset($_REQUEST['Contacts0emailAddressId0']);
        unset($_POST['Contacts0emailAddressVerifiedFlag0']);
        unset($_REQUEST['Contacts0emailAddressVerifiedFlag0']);
        unset($_POST['Contacts0emailAddressVerifiedValue0']);
        unset($_REQUEST['Contacts0emailAddressVerifiedValue0']);

        // Make sure we can clean up the new email address.
        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress($address2);

        $this->assertCount(
            1,
            $bean->emailAddress->addresses,
            'The bean should still have one email address'
        );
        $this->assertNotEquals(
            $address1,
            $bean->emailAddress->addresses[0]['email_address'],
            'The email address should not be address1'
        );
        $this->assertNotEquals(
            $ea->id,
            $bean->emailAddress->addresses[0]['email_address_id'],
            'The email address should not be the same as the old one'
        );
        $this->assertEquals(
            $address2,
            $bean->emailAddress->addresses[0]['email_address'],
            'The email address should be address2'
        );
    }

    /**
     * @covers SugarEmailAddress::populateAddresses
     * @covers SugarEmailAddress::addAddress
     * @covers SugarEmailAddress::getEmailGUID
     * @covers SugarEmailAddress::getGuid
     */
    public function testPopulateAddresses_UpdatesExistingEmailAddress()
    {
        $address = Uuid::uuid4() . '@example.com';
        $ea = SugarTestEmailAddressUtilities::createEmailAddress($address);

        $bean = BeanFactory::newBean('Contacts');
        $bean->emailAddress->addAddress($address);

        $this->assertCount(
            1,
            $bean->emailAddress->addresses,
            'The bean should have one email address'
        );

        // Change the bean's email address.
        // The ID and email address are not in sync. The address is different. The ID is still passed but it is ignored.
        $_POST['Contacts_email_widget_id'] = $_REQUEST['Contacts_email_widget_id'] = 0;
        $_POST['emailAddressWidget'] = $_REQUEST['emailAddressWidget'] = 1;
        $_POST['useEmailWidget'] = $_REQUEST['useEmailWidget'] = true;
        $_POST['Contacts0emailAddress0'] = $_REQUEST['Contacts0emailAddress0'] = $address;
        $_POST['Contacts0emailAddressId0'] = $_REQUEST['Contacts0emailAddressId0'] = $ea->id;
        $_POST['Contacts0emailAddressInvalidFlag'] = $_REQUEST['Contacts0emailAddressInvalidFlag'] = ['Contacts0emailAddress0'];
        $_POST['Contacts0emailAddressVerifiedFlag0'] = $_REQUEST['Contacts0emailAddressVerifiedFlag0'] = true;
        $_POST['Contacts0emailAddressVerifiedValue0'] = $_REQUEST['Contacts0emailAddressVerifiedValue0'] = $address;

        $bean->emailAddress->populateAddresses($bean->id, $bean->module_name);

        unset($_POST['Contacts_email_widget_id']);
        unset($_REQUEST['Contacts_email_widget_id']);
        unset($_POST['emailAddressWidget']);
        unset($_REQUEST['emailAddressWidget']);
        unset($_POST['useEmailWidget']);
        unset($_REQUEST['useEmailWidget']);
        unset($_POST['Contacts0emailAddress0']);
        unset($_REQUEST['Contacts0emailAddress0']);
        unset($_POST['Contacts0emailAddressId0']);
        unset($_REQUEST['Contacts0emailAddressId0']);
        unset($_POST['Contacts0emailAddressInvalidFlag']);
        unset($_REQUEST['Contacts0emailAddressInvalidFlag']);
        unset($_POST['Contacts0emailAddressVerifiedFlag0']);
        unset($_REQUEST['Contacts0emailAddressVerifiedFlag0']);
        unset($_POST['Contacts0emailAddressVerifiedValue0']);
        unset($_REQUEST['Contacts0emailAddressVerifiedValue0']);

        $this->assertCount(
            1,
            $bean->emailAddress->addresses,
            'The bean should still have one email address'
        );
        $this->assertEquals(
            $ea->id,
            $bean->emailAddress->addresses[0]['email_address_id'],
            'The email address should be the same'
        );
        $this->assertEquals(
            $address,
            $bean->emailAddress->addresses[0]['email_address'],
            'The email address should still be address'
        );
        $this->assertEquals(
            1,
            $bean->emailAddress->addresses[0]['invalid_email'],
            'The email address should be invalid'
        );
    }
}
