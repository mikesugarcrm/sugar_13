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

/**
 * @coversDefaultClass ContactsApi
 */
class ContactsApiTest extends TestCase
{
    private $api;
    private $contactsApi;
    private $configOptoutBackUp;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp(): void
    {
        if (isset($GLOBALS['sugar_config']['new_email_addresses_opted_out'])) {
            $this->configOptoutBackUp = $GLOBALS['sugar_config']['new_email_addresses_opted_out'];
        }

        $this->api = SugarTestRestUtilities::getRestServiceMock();
        $this->api->user = $GLOBALS['current_user'];
        $this->contactsApi = new ContactsApi();
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();

        if (isset($this->configOptoutBackUp)) {
            $GLOBALS['sugar_config']['new_email_addresses_opted_out'] = $this->configOptoutBackUp;
        } else {
            unset($GLOBALS['sugar_config']['new_email_addresses_opted_out']);
        }
    }

    public function contactDataProvider()
    {
        $uuid = Uuid::uuid1();
        $email = "foo_{$uuid}@bar.biz";

        return [
            [
                $email,
                true,
                false,
                false,
            ],
            [
                $email,
                true,
                true,
                false,
            ],
            [
                $email,
                true,
                false,
                true,
            ],
            [
                $email,
                true,
                true,
                true,
            ],
            [
                $email,
                false,
                false,
                false,
            ],
            [
                $email,
                false,
                true,
                false,
            ],
            [
                $email,
                false,
                false,
                true,
            ],
            [
                $email,
                false,
                true,
                true,
            ],
        ];
    }

    /**
     * Create Contact Record with supplied Email properties
     * @dataProvider contactDataProvider
     * @covers ::createRecord
     * @param string $email
     * @param boolean $primary
     * @param boolean $optOut
     * @param boolean $invalidEmail
     */
    public function testCreateContact_EmailPropertiesSupplied($email, $primary, $optOut, $invalidEmail)
    {
        $args = [
            'module' => 'Contacts',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => [
                [
                    'email_address' => $email,
                    'primary_address' => $primary,
                    'opt_out' => $optOut,
                    'invalid_email' => $invalidEmail,
                ],
            ],
        ];

        $result = $this->contactsApi->createRecord($this->api, $args);
        SugarTestContactUtilities::setCreatedContact([$result['id']]);

        $this->assertNotEmpty($result['email'][0]['email_address_id'], 'Email Address Id should not be empty');
        $this->assertSame($email, $result['email'][0]['email_address'], 'email_address should match');
        $this->assertSame($primary, $result['email'][0]['primary_address'], 'primary property should match');
        $this->assertSame($optOut, $result['email'][0]['opt_out'], 'opt_out property should match');
        $this->assertSame($invalidEmail, $result['email'][0]['invalid_email'], 'invalid_email property should match');
    }

    public function optoutDataProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * Create Contact Record with Email Address - Optout value is defaulted based on Configuration
     *
     * @covers ::createRecord
     * @dataProvider optoutDataProvider
     */
    public function testCreateContactWithEmail_OptoutPropertyShouldDefaultToOptedIn(bool $defaultOptout)
    {
        $GLOBALS['sugar_config']['new_email_addresses_opted_out'] = $defaultOptout;

        $email = 'email_' . Uuid::uuid1() . '@bar.biz';
        $args = [
            'module' => 'Contacts',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => [
                [
                    'email_address' => $email,
                ],
            ],
        ];
        $result = $this->contactsApi->createRecord($this->api, $args);
        SugarTestContactUtilities::setCreatedContact([$result['id']]);

        $this->assertNotEmpty($result['email'][0]['email_address_id'], 'Email Address Id should not be empty');
        $this->assertSame($email, $result['email'][0]['email_address'], 'email_address should match');
        $this->assertSame($defaultOptout, $result['email'][0]['opt_out'], 'Email opt_out value does not match config');
    }
}
