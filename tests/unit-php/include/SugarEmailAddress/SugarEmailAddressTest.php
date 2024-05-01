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

namespace Sugarcrm\SugarcrmTestsUnit\inc\SugarEmailAddress;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \SugarEmailAddress
 */
class SugarEmailAddressTest extends TestCase
{
    /**
     * @covers       ::removeAddressById
     * @dataProvider removeAddressByIdProvider
     *
     * @param array $startingAddresses
     * @param string $removalId
     * @param array $expectedAddresses
     */
    public function testRemoveEmailAddressById(array $startingAddresses, string $removalId, array $expectedAddresses)
    {
        $sea = $this->getMockBuilder(\SugarEmailAddress::class)->disableOriginalConstructor()->setMethods()->getMock();
        $sea->addresses = $startingAddresses;
        $sea->removeAddressById($removalId);

        $this->assertSame($expectedAddresses, $sea->addresses);
    }

    public function removeAddressByIdProvider()
    {
        return [
            //Basic Test
            [
                //starting addresses
                [
                    [
                        'email_address_id' => 'eid1',
                        'email_address' => 'foo@example.com',
                        'primary_address' => true,
                        'invalid_email' => false,
                    ],
                    [
                        'email_address_id' => 'eid2',
                        'email_address' => 'bar@example.com',
                        'primary_address' => false,
                        'invalid_email' => false,
                    ],
                    [
                        'email_address_id' => 'eid3',
                        'email_address' => 'baz@example.com',
                        'primary_address' => false,
                        'invalid_email' => false,
                    ],
                ],
                //id to remove
                'eid2',
                //resulting addresses
                [
                    [
                        'email_address_id' => 'eid1',
                        'email_address' => 'foo@example.com',
                        'primary_address' => true,
                        'invalid_email' => false,
                    ],
                    [
                        'email_address_id' => 'eid3',
                        'email_address' => 'baz@example.com',
                        'primary_address' => false,
                        'invalid_email' => false,
                    ],
                ],
            ],
            //Changing the primary email
            [
                //starting addresses
                [
                    [
                        'email_address_id' => 'eid1',
                        'email_address' => 'foo@example.com',
                        'primary_address' => true,
                        'invalid_email' => false,
                    ],
                    [
                        'email_address_id' => 'eid2',
                        'email_address' => 'bar@example.com',
                        'primary_address' => false,
                        'invalid_email' => true,
                    ],
                    [
                        'email_address_id' => 'eid3',
                        'email_address' => 'baz@example.com',
                        'primary_address' => false,
                        'invalid_email' => false,
                    ],
                ],
                //id to remove
                'eid1',
                //resulting addresses
                [
                    [
                        'email_address_id' => 'eid2',
                        'email_address' => 'bar@example.com',
                        'primary_address' => false,
                        'invalid_email' => true,
                    ],
                    [
                        'email_address_id' => 'eid3',
                        'email_address' => 'baz@example.com',
                        'primary_address' => true,
                        'invalid_email' => false,
                    ],
                ],
            ],
            //Changing the primary email with all invalid
            [
                //starting addresses
                [
                    [
                        'email_address_id' => 'eid1',
                        'email_address' => 'foo@example.com',
                        'primary_address' => true,
                        'invalid_email' => false,
                    ],
                    [
                        'email_address_id' => 'eid2',
                        'email_address' => 'bar@example.com',
                        'primary_address' => false,
                        'invalid_email' => true,
                    ],
                    [
                        'email_address_id' => 'eid3',
                        'email_address' => 'baz@example.com',
                        'primary_address' => false,
                        'invalid_email' => true,
                    ],
                ],
                //id to remove
                'eid1',
                //resulting addresses
                [
                    [
                        'email_address_id' => 'eid2',
                        'email_address' => 'bar@example.com',
                        'primary_address' => true,
                        'invalid_email' => true,
                    ],
                    [
                        'email_address_id' => 'eid3',
                        'email_address' => 'baz@example.com',
                        'primary_address' => false,
                        'invalid_email' => true,
                    ],
                ],
            ],
            //Removing the last email
            [
                //starting addresses
                [
                    [
                        'email_address_id' => 'eid1',
                        'email_address' => 'foo@example.com',
                        'primary_address' => true,
                        'invalid_email' => false,
                    ],
                ],
                //id to remove
                'eid1',
                //resulting addresses
                [],
            ],
        ];
    }

    /**
     * @covers       ::removeAddressById
     * @dataProvider removeEmailAddressProvider
     *
     * @param array $startingAddresses
     * @param string $removalAddr
     * @param array $expectedAddresses
     */
    public function testRemoveEmailAddress(array $startingAddresses, string $removalAddr, array $expectedAddresses)
    {

        $sea = $this->getMockBuilder(\SugarEmailAddress::class)->disableOriginalConstructor()->setMethods()->getMock();
        $sea->addresses = $startingAddresses;
        $sea->removeAddress($removalAddr);

        $this->assertSame($expectedAddresses, $sea->addresses);
    }

    public function removeEmailAddressProvider()
    {
        return [
            //Basic Test
            [
                //starting addresses
                [
                    [
                        'email_address_id' => 'eid1',
                        'email_address' => 'foo@example.com',
                        'primary_address' => true,
                        'invalid_email' => false,
                    ],
                    [
                        'email_address_id' => 'eid2',
                        'email_address' => 'bar@example.com',
                        'primary_address' => false,
                        'invalid_email' => false,
                    ],
                    [
                        'email_address_id' => 'eid3',
                        'email_address' => 'baz@example.com',
                        'primary_address' => false,
                        'invalid_email' => false,
                    ],
                ],
                //addr to remove
                'bar@example.com',
                //resulting addresses
                [
                    [
                        'email_address_id' => 'eid1',
                        'email_address' => 'foo@example.com',
                        'primary_address' => true,
                        'invalid_email' => false,
                    ],
                    [
                        'email_address_id' => 'eid3',
                        'email_address' => 'baz@example.com',
                        'primary_address' => false,
                        'invalid_email' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::removeLegacyAddressForBean
     *
     * @dataProvider providerTestRemoveLegacyAddressForBean
     */
    public function testRemoveLegacyAddressForBean(?string $legacyEmail, $addressToRemove, $expected)
    {
        $bean = $this->createMock(\SugarBean::class);
        $bean->email1 = $legacyEmail;

        $sea = $this->getMockBuilder(\SugarEmailAddress::class)->disableOriginalConstructor()->setMethods()->getMock();

        $sea->removeLegacyAddressForBean($bean, $addressToRemove);

        $this->assertEquals($expected, empty($bean->email1));
    }

    public function providerTestRemoveLegacyAddressForBean()
    {
        return [
            ['same@t.com', 'same@t.com', true],
            ['not_same@t.com', 'same@t.com', false],
            [null, 'same@t.com', true],
        ];
    }
}
