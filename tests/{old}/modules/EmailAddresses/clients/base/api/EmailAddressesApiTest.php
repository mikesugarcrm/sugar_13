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
 * @coversDefaultClass EmailAddressesApi
 */
class EmailAddressesApiTest extends TestCase
{
    protected $service;
    private $configOptoutBackUp;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
    }

    protected function setUp(): void
    {
        if (isset($GLOBALS['sugar_config']['new_email_addresses_opted_out'])) {
            $this->configOptoutBackUp = $GLOBALS['sugar_config']['new_email_addresses_opted_out'];
        }

        $this->service = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown(): void
    {
        if (isset($this->configOptoutBackUp)) {
            $GLOBALS['sugar_config']['new_email_addresses_opted_out'] = $this->configOptoutBackUp;
        } else {
            unset($GLOBALS['sugar_config']['new_email_addresses_opted_out']);
        }
    }

    /**
     * @covers ::createBean
     */
    public function testCreateBean_CannotCreateWithoutAnEmailAddress()
    {
        $api = new EmailAddressesApi();
        $args = [
            'module' => 'EmailAddresses',
        ];

        $this->expectException(SugarApiExceptionMissingParameter::class);
        $api->createBean($this->service, $args);
    }

    /**
     * @covers ::createBean
     */
    public function testCreateBean_CannotCreateWithAnInvalidEmailAddress()
    {
        $api = new EmailAddressesApi();
        $args = [
            'module' => 'EmailAddresses',
            'email_address' => 'a',
        ];

        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $api->createBean($this->service, $args);
    }

    /**
     * @covers ::createBean
     */
    public function testCreateBean_CreateNewEmailAddress()
    {
        $address = 'address-' . Uuid::uuid1() . '@example.com';

        $ea = new SugarEmailAddress();
        $this->assertEmpty($ea->getGuid($address), "{$address} should not already exist");

        $api = $this->getMockBuilder('EmailAddressesApi')
            ->setMethods(['updateRecord'])
            ->getMock();
        $api->expects($this->never())->method('updateRecord');

        $args = [
            'module' => 'EmailAddresses',
            'email_address' => $address,
            'email_address_caps' => 'foo@bar.com',
        ];
        $bean = $api->createBean($this->service, $args);

        $this->assertNotEmpty($bean->id);
        SugarTestEmailAddressUtilities::setCreatedEmailAddress($bean->id);

        $this->assertSame($address, $bean->email_address);
        $this->assertSame(sugarStrToUpper($address), $bean->email_address_caps);
    }

    /**
     * @covers ::createBean
     */
    public function testCreateBean_ReturnExistingEmailAddressWithoutMakingChanges()
    {
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $api = new EmailAddressesApi();
        $args = [
            'module' => 'EmailAddresses',
            'email_address' => $address->email_address,
            'invalid_email' => true,
        ];
        $bean = $api->createBean($this->service, $args);

        $this->assertSame($address->id, $bean->id);
        $this->assertSame($address->email_address, $bean->email_address);
        $this->assertFalse(boolval($bean->invalid_email));
    }

    /**
     * @covers ::updateRecord
     */
    public function testUpdateRecord_EmailAddressDoesNotChange()
    {
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $this->assertFalse($address->invalid_email);

        $api = new EmailAddressesApi();
        $args = [
            'module' => 'EmailAddresses',
            'record' => $address->id,
            'email_address' => 'foo@bar.com',
            'invalid_email' => true,
        ];
        $record = $api->updateRecord($this->service, $args);

        $this->assertSame($address->email_address, $record['email_address']);
        $this->assertTrue($record['invalid_email']);
    }

    public function optoutDataProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @covers ::createBean
     * @dataProvider optoutDataProvider
     */
    public function testCreateBean_CreateNewEmailAddress_OptOutValueComesFromConfiguredDefault(bool $optOut)
    {
        $GLOBALS['sugar_config']['new_email_addresses_opted_out'] = $optOut;
        $address = 'address-' . Uuid::uuid1() . '@example.com';

        $api = new EmailAddressesApi();
        $args = [
            'module' => 'EmailAddresses',
            'email_address' => $address,
            'email_address_caps' => sugarStrToUpper($address),
        ];
        $bean = $api->createBean($this->service, $args);

        $this->assertNotEmpty($bean->id);
        SugarTestEmailAddressUtilities::setCreatedEmailAddress($bean->id);

        $this->assertEquals($optOut, boolval($bean->opt_out), 'New email opt_out does not match configured default');
    }
}
