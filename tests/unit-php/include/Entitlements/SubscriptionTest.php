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

namespace Sugarcrm\SugarcrmTestsUnit\inc\Entitlements;

use Exception;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Entitlements\Subscription;
use Sugarcrm\Sugarcrm\inc\Entitlements\Exception\SubscriptionException;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class SubscriptionTest
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Entitlements\Subscription
 */
class SubscriptionTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::parse
     */
    public function testGetDataWithInvalidJson(): void
    {
        $this->expectException(SubscriptionException::class);
        new Subscription('{');
    }

    /**
     * @covers ::__construct
     * @covers ::parse
     * @covers ::__get
     *
     * @dataProvider subscriptionProvider
     */
    public function testGetData($data, $expected, $expectedtAddonCount)
    {
        $subscription = new Subscription($data);
        $this->assertSame($expected['id'], $subscription->id);
        foreach ($expected as $key => $value) {
            $this->assertSame($value, $subscription->$key, "failed on property: $key!");
        }

        // addons
        if ($expectedtAddonCount > 0) {
            $this->assertSame($expectedtAddonCount, count($subscription->addons));
        }
        // not property
        $this->assertEmpty($subscription->xyz);
    }

    public function subscriptionProvider()
    {
        // @codingStandardsIgnoreStart
        return [
            [
                '{"success":true,"error":"","subscription":{"id":"914f07ac-3acb-3a3a-8d4f-570fe8dcae78","debug":0,"addons":{"11d7e3f8-ed89-f588-e9af-4dbf44a9b207":{"quantity":"150","product_name":"iPad with offline sync","expiration_date":4102473600},"37f53940-8ca0-e49a-5b11-4dbf4499a788":{"quantity":"150","product_name":"Blackberry with offline sync","expiration_date":4102473600},"4052c256-ab6c-6111-b6f8-4dbf44ae8408":{"quantity":"150","product_name":"Sugar Plug-in for Lotus Notes","expiration_date":4102473600},"b0fade74-2556-d181-83c7-4dbf44ee21fa":{"quantity":"150","product_name":"iPhone with offline sync","expiration_date":4102473600}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"e6e4d734-ce3c-2163-b218-4942c7410ef0","quantity_c":150,"account_name":"SugarCRM Partner Portal Login","account_type":"Partner","date_entered":1460685667,"evaluation_c":0,"portal_users":150,"date_modified":1554170401,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"5fd99624e58ec184c96d0520d9ab8b2d","term_end_date_c":4102473600,"term_start_date_c":1460617200,"enforce_user_limit":0,"od_instance_name_c":"qatest","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'id' => '914f07ac-3acb-3a3a-8d4f-570fe8dcae78',
                    'account_name' => 'SugarCRM Partner Portal Login',
                    'product' => 'ENT',
                    'subscription_id' => '5fd99624e58ec184c96d0520d9ab8b2d',
                    'ignore_expiration_date' => 0,
                    'quantity_c' => 150,
                ],
                4,
            ],
            [
                '{"no subscription section": {"quantity" : "100"}}',
                [
                    'id' => null,
                    'quantity' => null],
                0,
            ],
            [
                '{"success":true,"error":"","subscription":{"id":"ffffc6a2-6ac3-11e9-b0f5-02c10f456dba","debug":0,"addons":[],"emails":[],"status":"enabled","audited":1,"domains":[],"product":"","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":10,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597765,"evaluation_c":0,"portal_users":0,"date_modified":1556597789,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"47fa5aa6620415261cd7bcd2a8de6d31","term_end_date_c":4102473600,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"aa8834fa-6ac0-11e9-b588-02c10f456dba","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'id' => 'ffffc6a2-6ac3-11e9-b0f5-02c10f456dba',
                    'account_name' => 'iApps Test Partner Account',
                    'product' => '',
                    'subscription_id' => '47fa5aa6620415261cd7bcd2a8de6d31',
                    'ignore_expiration_date' => 0,
                    'quantity_c' => 10,
                ],
                0,
            ]
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * @covers ::__construct
     * @covers ::parse
     *
     * @dataProvider subscriptionExceptionProvider
     */
    public function testGetDataException($data)
    {
        $this->expectException(Exception::class);
        new Subscription($data);
    }

    public function subscriptionExceptionProvider()
    {
        return [
            ['{"subscription": {"no_id" : "100"}'],
        ];
    }

    /**
     * @covers ::getDefaultSubscription
     *
     * @param $quantity
     * @param $exirationDate
     * @param $expected
     *
     * @dataProvider getDefaultSubscriptionProvider
     */
    public function testGetDefaultSubscription($quantity, $exirationDate, $expected)
    {
        $this->iniSet('date.timezone', 'America/Los_Angeles');

        $subscriptionMock = $this->getMockBuilder(Subscription::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLicenseSettingByKey'])
            ->getMock();

        $subscriptionMock->expects($this->any())
            ->method('getLicenseSettingByKey')
            ->willReturnMap([
                ['license_users', 1, $quantity],
                ['license_expire_date', '+12 months', $exirationDate],
            ]);

        $sub = TestReflection::callProtectedMethod($subscriptionMock, 'getDefaultSubscription', []);
        $this->assertSame($expected, $sub);
    }

    public function getDefaultSubscriptionProvider()
    {
        return [
            'normal' => [
                1000,
                '2100-01-01',
                [
                    'quantity' => 1000,
                    'expiration_date' => 4102473600,
                    'start_date' => null,
                    'customer_product_name' => 'SugarCRM',
                    'bundled_products' => [],
                ],
            ],
            'expired' => [
                1000,
                '2020-04-05',
                [],
            ],
        ];
    }

    /**
     * @covers ::getSubscriptions
     * @covers ::getSubscriptionFromAddons
     * @covers ::parse
     * @covers ::__get
     *
     * @dataProvider getSubscriptionsProvider
     */
    public function testGetSubscriptions($data, $expected)
    {
        $subscription = new Subscription($data);
        $this->assertSame($expected, $subscription->getSubscriptions());
    }

    public function getSubscriptionsProvider(): array
    {
        // @codingStandardsIgnoreStart
        return [
            'SERVE Only no quatity value' => [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"product_name":"Service Cloud (DEV ONLY)","expiration_date":4102473600}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":0,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1556597786,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":4102473600,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [],
            ],
            'SERVE Only, expired' => [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Service Cloud (DEV ONLY)","expiration_date":1287798000}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":0,"account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1556597786,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":4102473600,"term_start_date_c":1556175600,"enforce_user_limit":1,"od_instance_name_c":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [],
            ],
            'no subscription section' => [
                '{"no subscription section": {"quantity" : "100"}}',
                [],
            ],
            'SERVE + ENT + HINT' => [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"11d7e3f8-ed89-f588-e9af-4dbf44a9b207":{"quantity":"100","product_name":"iPad with offline sync","start_date_c":1556175600,"product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":""},"37f53940-8ca0-e49a-5b11-4dbf4499a788":{"quantity":"100","product_name":"Blackberry with offline sync","start_date_c":1556175600,"product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":""},"4052c256-ab6c-6111-b6f8-4dbf44ae8408":{"quantity":"100","product_name":"Sugar Plug-in for Lotus Notes","start_date_c":1556175600,"product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":"Ent"},"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Sugar Serve","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":"Ent"},"b0fade74-2556-d181-83c7-4dbf44ee21fa":{"quantity":"100","product_name":"iPhone with offline sync","start_date_c":1556175600,"product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":100,"product_name":"Sugar Enterprise","start_date_c":1556175600,"product_code_c":"ENT","expiration_date":4102473600,"deployment_flavor_c":"Ent"},"bcfc93c6-3cb2-11e7-8335-d4bed9b6dbe0":{"quantity":15,"product_name":"SugarCRM Hint (Evaluation)","start_date_c":1558335600,"product_code_c":"HINT","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":"100","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1564624801,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'CURRENT' => [
                        'quantity' => 100,
                        'expiration_date' => 4102473600,
                        'start_date' => 1556175600,
                        'customer_product_name' => null,
                        'bundled_products' => [],
                    ],
                    'SUGAR_SERVE' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1556175600,
                        'customer_product_name' => null,
                        'bundled_products' => [],
                    ],
                    'HINT' => [
                        'quantity' => 15,
                        'expiration_date' => 4102473600,
                        'start_date' => 1558335600,
                        'customer_product_name' => null,
                        'bundled_products' => [],
                    ],
                ],
            ],
            'SERVE + ENT with SERVE is expired' => [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"11d7e3f8-ed89-f588-e9af-4dbf44a9b207":{"quantity":"100","product_name":"iPad with offline sync","start_date_c":1556175600,"product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":""},"37f53940-8ca0-e49a-5b11-4dbf4499a788":{"quantity":"100","product_name":"Blackberry with offline sync","start_date_c":1556175600,"product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":""},"4052c256-ab6c-6111-b6f8-4dbf44ae8408":{"quantity":"100","product_name":"Sugar Plug-in for Lotus Notes","start_date_c":1556175600,"product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":"Ent"},"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Sugar Serve","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":1487798000,"deployment_flavor_c":"Ent"},"b0fade74-2556-d181-83c7-4dbf44ee21fa":{"quantity":"100","product_name":"iPhone with offline sync","start_date_c":1556175600,"product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":100,"product_name":"Sugar Enterprise","start_date_c":1556175600,"product_code_c":"ENT","expiration_date":4102473600,"deployment_flavor_c":"Ent"},"bcfc93c6-3cb2-11e7-8335-d4bed9b6dbe0":{"quantity":15,"product_name":"SugarCRM Hint (Evaluation)","start_date_c":1558335600,"product_code_c":"HINT","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","quantity_c":"100","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1564624801,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'CURRENT' => [
                        'quantity' => 100,
                        'expiration_date' => 4102473600,
                        'start_date' => 1556175600,
                        'customer_product_name' => null,
                        'bundled_products' => [],
                    ],
                    'HINT' => [
                        'quantity' => 15,
                        'expiration_date' => 4102473600,
                        'start_date' => 1558335600,
                        'customer_product_name' => null,
                        'bundled_products' => [],
                    ],
                ],
            ],
            'SERVE + SELL + ENT' => [
                '{"success":true,"error":"","subscription":{"id":"68ad7ebd-d522-67e2-6aea-570fe9baf420","debug":0,"addons":{"11d7e3f8-ed89-f588-e9af-4dbf44a9b207":{"quantity":"150","product_name":"iPad with offline sync","start_date_c":1460617200,"product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":""},"181aee1c-7b3e-11e9-b962-02c10f456dba":{"quantity":150,"product_name":"Sugar Sell","start_date_c":1563174000,"product_code_c":"SELL","expiration_date":4102473600,"deployment_flavor_c":"Ent"},"37f53940-8ca0-e49a-5b11-4dbf4499a788":{"quantity":"150","product_name":"Blackberry with offline sync","start_date_c":1460617200,"product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":""},"4052c256-ab6c-6111-b6f8-4dbf44ae8408":{"quantity":150,"product_name":"Sugar Plug-in for Lotus Notes","start_date_c":"","product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":"Ent"},"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":150,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":"","product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":""},"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":150,"product_name":"Sugar Serve","start_date_c":1563174000,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":"Ent"},"b0fade74-2556-d181-83c7-4dbf44ee21fa":{"quantity":"150","product_name":"iPhone with offline sync","start_date_c":1460617200,"product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":150,"product_name":"Sugar Enterprise","start_date_c":1460617200,"product_code_c":"ENT","expiration_date":4102473600,"deployment_flavor_c":"Ent"}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"e6e4d734-ce3c-2163-b218-4942c7410ef0","quantity_c":"150","account_name":"SugarCRM Partner Portal Login","account_type":"Partner","date_entered":1460685883,"evaluation_c":0,"portal_users":150,"date_modified":1566439202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"4ba82b21756db68afbcdcc76214ec577","term_end_date_c":4102473600,"term_start_date_c":1460617200,"account_partner_id":"","enforce_user_limit":0,"od_instance_name_c":"qatest","account_partner_name":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'CURRENT' => [
                        'quantity' => 150,
                        'expiration_date' => 4102473600,
                        'start_date' => 1460617200,
                        'customer_product_name' => null,
                        'bundled_products' => [],
                    ],
                    'SUGAR_SELL' => [
                        'quantity' => 150,
                        'expiration_date' => 4102473600,
                        'start_date' => 1563174000,
                        'customer_product_name' => null,
                        'bundled_products' => [],
                    ],
                    'SUGAR_SERVE' => [
                        'quantity' => 150,
                        'expiration_date' => 4102473600,
                        'start_date' => 1563174000,
                        'customer_product_name' => null,
                        'bundled_products' => [],
                    ],
                ],
            ],
            'ENT + SERVE no quantity value for SERVE' => [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":100,"product_name":"Sugar Enterprise","start_date_c":1556175600,"product_code_c":"ENT","expiration_date":4102473600,"deployment_flavor_c":"Ent"}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'CURRENT' => [
                        'quantity' => 100,
                        'expiration_date' => 4102473600,
                        'start_date' => 1556175600,
                        'customer_product_name' => null,
                        'bundled_products' => [],
                    ],
                ],
            ],
            'ENT + SERVE' => [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":100,"product_name":"Sugar Enterprise","start_date_c":1556175600,"product_code_c":"ENT","expiration_date":4102473600,"deployment_flavor_c":"Ent"}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'CURRENT' => [
                        'quantity' => 100,
                        'expiration_date' => 4102473600,
                        'start_date' => 1556175600,
                        'customer_product_name' => null,
                        'bundled_products' => [],
                    ],
                    'SUGAR_SERVE' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1556175600,
                        'customer_product_name' => null,
                        'bundled_products' => [],
                    ],
                ],
            ],
            'SERVE only' => [
                '{"success":true,"error":"","subscription":{"id":"ffffc6a2-6ac3-11e9-b0f5-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597765,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"47fa5aa6620415261cd7bcd2a8de6d31","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'SUGAR_SERVE' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1556175600,
                        'customer_product_name' => null,
                        'bundled_products' => [],
                    ],
                ],
            ],
            'SELL only' => [
                '{"success":true,"error":"","subscription":{"id":"3efc5dc4-7b50-11e9-9f42-02c10f456dba","debug":0,"addons":{"181aee1c-7b3e-11e9-b962-02c10f456aaa":{"quantity":10,"product_name":"Sugar Sell (DEV ONLY)","start_date_c":1558335600,"product_code_c":"SELL","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1558417219,"evaluation_c":0,"portal_users":0,"date_modified":1558487575,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"3779135395d186056bbcc895dc3cfc00","term_end_date_c":4102473600,"term_start_date_c":1558335600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'SUGAR_SELL' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1558335600,
                        'customer_product_name' => null,
                        'bundled_products' => [],
                    ],
                ],
            ],
            'ENT only multiple addons with the same product_code_c' => [
                '{"success":true,"error":"","subscription":{"id":"914f07ac-3acb-3a3a-8d4f-570fe8dcae78","debug":0,"addons":{"11d7e3f8-ed89-f588-e9af-4dbf44a9b207":{"quantity":"150","product_name":"iPad with offline sync","start_date_c":"","expiration_date":4102473600,"product_code_c":""},"37f53940-8ca0-e49a-5b11-4dbf4499a788":{"quantity":"150","product_name":"Blackberry with offline sync","start_date_c":"","expiration_date":4102473600,"product_code_c":""},"4052c256-ab6c-6111-b6f8-4dbf44ae8408":{"quantity":"150","product_name":"Sugar Plug-in for Lotus Notes","start_date_c":"1460617200","expiration_date":4102473600,"product_code_c":"Ent"},"b0fade74-2556-d181-83c7-4dbf44ee21fa":{"quantity":"150","product_name":"iPhone with offline sync","start_date_c":"","expiration_date":4102473600,"product_code_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":150,"product_name":"Sugar Enterprise","start_date_c":1460617200,"expiration_date":4102473600,"product_code_c":"Ent"}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"e6e4d734-ce3c-2163-b218-4942c7410ef0","account_name":"SugarCRM Partner Portal Login","account_type":"Partner","date_entered":1460685667,"evaluation_c":0,"portal_users":150,"date_modified":1558836002,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"5fd99624e58ec184c96d0520d9ab8b2d","term_end_date_c":4102473600,"term_start_date_c":1460617200,"enforce_user_limit":0,"od_instance_name_c":"qatest","enforce_portal_users":0,"ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'CURRENT' => [
                        'quantity' => 300,
                        'expiration_date' => 4102473600,
                        'start_date' => 1460617200,
                        'customer_product_name' => null,
                        'bundled_products' => [],
                    ],
                ],
            ],
            'ENT + SERVE, multiple ENT product_code_c' => [
                '{"success":true,"error":"","subscription":{"id":"7bf0333a-c462-11e9-8e37-0242ac120008","debug":0,"addons":{"11d7e3f8-ed89-f588-e9af-4dbf44a9b207":{"quantity":14,"product_name":"iPad with offline sync","start_date_c":1566370800,"product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":""},"2320bbd8-7ede-abd0-8f2f-52a261a992cf":{"quantity":1,"product_name":"Partner Membership","start_date_c":1566370800,"product_code_c":"ENT","expiration_date":4102473600,"deployment_flavor_c":"Ent"},"37f53940-8ca0-e49a-5b11-4dbf4499a788":{"quantity":14,"product_name":"Blackberry with offline sync","start_date_c":1566370800,"product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":""},"387b2f92-ceeb-11e7-9c48-02c10f456dba":{"quantity":10,"product_name":"Partner Seats - Basic","start_date_c":1566370800,"product_code_c":"ENT","expiration_date":4102473600,"deployment_flavor_c":""},"4052c256-ab6c-6111-b6f8-4dbf44ae8408":{"quantity":14,"product_name":"Sugar Plug-in for Lotus Notes","start_date_c":1566370800,"product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":"Ent"},"9fc7525c-ceeb-11e7-aec6-02c10f456dba":{"quantity":4,"product_name":"Partner Seats - Additional","start_date_c":1566370800,"product_code_c":"ENT","expiration_date":4102473600,"deployment_flavor_c":""},"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":14,"product_name":"Sugar Serve","start_date_c":1566370800,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":"Ent"},"b0fade74-2556-d181-83c7-4dbf44ee21fa":{"quantity":14,"product_name":"iPhone with offline sync","start_date_c":1566370800,"product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":["jzhu@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Partner Membership","perpetual":0,"account_id":"16fce522-c462-11e9-bdfc-0242ac120008","quantity_c":14,"account_name":"JZ Corporation","account_type":"","date_entered":1566451486,"evaluation_c":0,"portal_users":100,"date_modified":1566451486,"partner_type_c":"","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"10f49eb1b862d3e031ea009f04717607","term_end_date_c":4102473600,"term_start_date_c":1566370800,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":1,"producttemplate_id_c":"2320bbd8-7ede-abd0-8f2f-52a261a992cf","account_managing_team":"","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'CURRENT' => [
                        'quantity' => 15,
                        'expiration_date' => 4102473600,
                        'start_date' => 1566370800,
                        'customer_product_name' => null,
                        'bundled_products' => [],
                    ],
                    'SUGAR_SERVE' => [
                        'quantity' => 14,
                        'expiration_date' => 4102473600,
                        'start_date' => 1566370800,
                        'customer_product_name' => null,
                        'bundled_products' => [],
                    ],
                ],
            ],
            'SELL + SERVE' => [
                '{"success":true,"error":"","subscription":{"id":"7387f7e2-7b50-11e9-9e70-02c10f456dba","debug":0,"addons":{"181aee1c-7b3e-11e9-b962-02c10f456dba":{"quantity":10,"product_name":"Sugar Sell (DEV ONLY)","start_date_c":1558335600,"product_code_c":"SELL","expiration_date":4102473600,"deployment_flavor_c":""},"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1558335600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1558417307,"evaluation_c":0,"portal_users":0,"date_modified":1558487606,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"944a2c9714859bed45493f69a95e6999","term_end_date_c":4102473600,"term_start_date_c":1558335600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [
                    'SUGAR_SELL' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1558335600,
                        'customer_product_name' => null,
                        'bundled_products' => [],
                    ],
                    'SUGAR_SERVE' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1558335600,
                        'customer_product_name' => null,
                        'bundled_products' => [],
                    ],
                ],
            ],
            'SELL PREMIER without product_code_c in bundled products' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":10,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":1639958400,"product_code_c":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Connector for LinkedIn Sales Navigator"},"product-template-id-for-sell-premiere":{"quantity":10,"product_name":"Sell Premiere","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1639958400,"product_code_c":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Discover"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1639958400,"product_code_c":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Connect"}},"product_edition_c":"SELL_PREMIER","deployment_flavor_c":"Ent","customer_product_name_c":"Sell Premiere"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell Premiere","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-premiere","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                [
                    'SUGAR_SELL_PREMIER_BUNDLE' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1639958400,
                        'customer_product_name' => 'Sell Premiere',
                        'bundled_products' => [],
                    ],
                ],
            ],
            'SELL PREMIER with one has product_code_c and one has not' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":10,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":1639958400,"product_code_c":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Connector for LinkedIn Sales Navigator"},"product-template-id-for-sell-premiere":{"quantity":10,"product_name":"Sell Premiere","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1639958400,"product_code_c":"DISCOVER","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Discover"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1639958400,"product_code_c":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Connect"}},"product_edition_c":"SELL_PREMIER","deployment_flavor_c":"Ent","customer_product_name_c":"Sell Premiere"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell Premiere","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-premiere","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                [
                    'SUGAR_SELL_PREMIER_BUNDLE' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1639958400,
                        'customer_product_name' => 'Sell Premiere',
                        'bundled_products' => [
                            'DISCOVER' => [
                                'quantity' => 10,
                                'expiration_date' => 4102473600,
                                'start_date' => 1639958400,
                                'customer_product_name' => 'Sugar Discover',
                                'bundled_products' => [],
                            ],
                        ],
                    ],
                ],
            ],
            'SELL PREMIER with product_code_c for all bundled products' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":10,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":1639958400,"product_code_c":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Connector for LinkedIn Sales Navigator"},"product-template-id-for-sell-premiere":{"quantity":10,"product_name":"Sell Premiere","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1639958400,"product_code_c":"DISCOVER","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Discover"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1639958400,"product_code_c":"CONNECT","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Connect"}},"product_edition_c":"SELL_PREMIER","deployment_flavor_c":"Ent","customer_product_name_c":"Sell Premiere"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell Premiere","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-premiere","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                [
                    'SUGAR_SELL_PREMIER_BUNDLE' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1639958400,
                        'customer_product_name' => 'Sell Premiere',
                        'bundled_products' => [
                            'CONNECT' => [
                                'quantity' => 10,
                                'expiration_date' => 4102473600,
                                'start_date' => 1639958400,
                                'customer_product_name' => 'Sugar Connect',
                                'bundled_products' => [],
                            ],
                            'DISCOVER' => [
                                'quantity' => 10,
                                'expiration_date' => 4102473600,
                                'start_date' => 1639958400,
                                'customer_product_name' => 'Sugar Discover',
                                'bundled_products' => [],
                            ],
                        ],
                    ],
                ],
            ],
            'SELL bundles with product_code_c for all bundled products but one has start date is not current yet' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":10,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":1639958400,"product_code_c":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Connector for LinkedIn Sales Navigator"},"product-template-id-for-sell-premiere":{"quantity":10,"product_name":"Sell Premiere","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1898580400,"product_code_c":"DISCOVER","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Discover"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1639958400,"product_code_c":"CONNECT","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Connect"}},"product_edition_c":"SELL_PREMIER","deployment_flavor_c":"Ent","customer_product_name_c":"Sell Premiere"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell Premiere","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-premiere","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                [
                    'SUGAR_SELL_PREMIER_BUNDLE' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1639958400,
                        'customer_product_name' => 'Sell Premiere',
                        'bundled_products' => [
                            'CONNECT' => [
                                'quantity' => 10,
                                'expiration_date' => 4102473600,
                                'start_date' => 1639958400,
                                'customer_product_name' => 'Sugar Connect',
                                'bundled_products' => [],
                            ],
                        ],
                    ],
                ],
            ],
            'SELL Premier with empty expiration_date, the expiration_date will be filled by parant' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":10,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":1639958400,"product_code_nc":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Connector for LinkedIn Sales Navigator"},"product-template-id-for-sell-premiere":{"quantity":10,"product_name":"Sell Premiere","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1639958400,"product_code_c":"DISCOVER","expiration_date":"","product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Discover"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1639958400,"product_code_c":"CONNECT","expiration_date":"","product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Connect"}},"product_edition_c":"SELL_PREMIER","deployment_flavor_c":"Ent","customer_product_name_c":"Sell Premiere"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell Premiere","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-premiere","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                [
                    'SUGAR_SELL_PREMIER_BUNDLE' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1639958400,
                        'customer_product_name' => 'Sell Premiere',
                        'bundled_products' => [
                            'CONNECT' => [
                                'quantity' => 10,
                                'expiration_date' => 4102473600,
                                'start_date' => 1639958400,
                                'customer_product_name' => 'Sugar Connect',
                                'bundled_products' => [],
                            ],
                            'DISCOVER' => [
                                'quantity' => 10,
                                'expiration_date' => 4102473600,
                                'start_date' => 1639958400,
                                'customer_product_name' => 'Sugar Discover',
                                'bundled_products' => [],
                            ],
                        ],
                    ],
                ],
            ],
            'SELL Essentials with product_code_c for all bundled products' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":10,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":1639958400,"product_code_c":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Connector for LinkedIn Sales Navigator"},"product-template-id-for-sell-premiere":{"quantity":10,"product_name":"Sell Premiere","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1639958400,"product_code_c":"DISCOVER","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Discover"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1639958400,"product_code_c":"CONNECT","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Connect"}},"product_edition_c":"SELL_ESSENTIALS","deployment_flavor_c":"Ent","customer_product_name_c":"Sell Premiere"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell Premiere","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-premiere","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                [
                    'SUGAR_SELL_ESSENTIALS' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1639958400,
                        'customer_product_name' => 'Sell Premiere',
                        'bundled_products' => [
                            'CONNECT' => [
                                'quantity' => 10,
                                'expiration_date' => 4102473600,
                                'start_date' => 1639958400,
                                'customer_product_name' => 'Sugar Connect',
                                'bundled_products' => [],
                            ],
                            'DISCOVER' => [
                                'quantity' => 10,
                                'expiration_date' => 4102473600,
                                'start_date' => 1639958400,
                                'customer_product_name' => 'Sugar Discover',
                                'bundled_products' => [],
                            ],
                        ],
                    ],
                ],
            ],
            'SELL bundle with unknown product_edition_c with product_code_c for all bundled products' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":10,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":1639958400,"product_code_c":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Connector for LinkedIn Sales Navigator"},"product-template-id-for-sell-premiere":{"quantity":10,"product_name":"Sell Premiere","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1639958400,"product_code_c":"DISCOVER","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Discover"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1639958400,"product_code_c":"CONNECT","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Connect"}},"product_edition_c":"SELL_NEW_PRODUCT","deployment_flavor_c":"Ent","customer_product_name_c":"Sell Premiere"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell Premiere","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-premiere","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                [
                    'SUGAR_SELL_BUNDLE' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1639958400,
                        'customer_product_name' => 'Sell Premiere',
                        'bundled_products' => [
                            'CONNECT' => [
                                'quantity' => 10,
                                'expiration_date' => 4102473600,
                                'start_date' => 1639958400,
                                'customer_product_name' => 'Sugar Connect',
                                'bundled_products' => [],
                            ],
                            'DISCOVER' => [
                                'quantity' => 10,
                                'expiration_date' => 4102473600,
                                'start_date' => 1639958400,
                                'customer_product_name' => 'Sugar Discover',
                                'bundled_products' => [],
                            ],
                        ],
                    ],
                ],
            ],
            'SELL with unknown product_edition_c and has no bundled products' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":10,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":1639958400,"product_code_c":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Connector for LinkedIn Sales Navigator"},"product-template-id-for-sell-premiere":{"quantity":10,"product_name":"Sell Unknow","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600, "product_edition_c":"SELL_NEW_PRODUCT","deployment_flavor_c":"Ent","customer_product_name_c":"Sell New Product"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell Premiere","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-premiere","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                [
                    'SUGAR_SELL' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1639958400,
                        'customer_product_name' => 'Sell New Product',
                        'bundled_products' => [],
                    ],
                ],
            ],
            'SELL with product_edition_c and has no bundled products' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"product-template-id-for-sell-premiere":{"quantity":10,"product_name":"Sell Unknow","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600, "product_edition_c":"SELL_ESSENTIALS","deployment_flavor_c":"Ent","customer_product_name_c":"Sell Essentials"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell Premiere","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-premiere","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                [
                    'SUGAR_SELL_ESSENTIALS' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1639958400,
                        'customer_product_name' => 'Sell Essentials',
                        'bundled_products' => [],
                    ],
                ],
            ],
            'SERVE Pluse' => [
                '{"success":true,"error":"","subscription":{"id":"68670ce2-ddf7-11ec-9c26-027f079f78e1","addons":{"23b54aaa-bd09-11ec-a5e7-029395602a62":{"quantity":10,"product_name":"Serve Add-On Package (for Sell Premier Customers)","start_date_c":1653609600,"product_code_c":"SERVE_ADDON_PACKAGE","expiration_date":4102473600,"bundled_products":{"3b10614c-c18e-11ec-9d49-06eddc549468":{"quantity":100,"product_name":"Sugar Maps","start_date_c":1653609600,"product_code_c":"MAPS","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"3e2e5147-257a-f562-8007-576ae9ab4d04":{"quantity":10,"product_name":"Customer Journey Plug-In","start_date_c":1653609600,"product_code_c":"","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1653609600,"product_code_c":"","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"bc19f754-3890-11eb-9074-02c10f456dba":{"quantity":10,"product_name":"Sugar Hint Applet","start_date_c":1653609600,"product_code_c":"HINT","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Hint"}},"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"92ef9670-9b24-11ec-a8fe-02fb813964b8":{"quantity":10,"product_name":"Sugar Sell Premier","start_date_c":1653609600,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"3b10614c-c18e-11ec-9d49-06eddc549468":{"quantity":10,"product_name":"Sugar Maps","start_date_c":1653609600,"product_code_c":"MAPS","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"3e2e5147-257a-f562-8007-576ae9ab4d04":{"quantity":10,"product_name":"Customer Journey Plug-In","start_date_c":1653609600,"product_code_c":"","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1653609600,"product_code_c":"","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"5604efdc-d92a-11ec-9875-06ef2d010d1d":{"quantity":10,"product_name":"Sugar Predict Premier","start_date_c":1653609600,"product_code_c":"PREDICT_PREMIER","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Predict"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1653609600,"product_code_c":"","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"bc19f754-3890-11eb-9074-02c10f456dba":{"quantity":10,"product_name":"Sugar Hint Applet","start_date_c":1653609600,"product_code_c":"HINT","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Hint"}},"product_edition_c":"SELL_PREMIER","deployment_flavor_c":"Ent","customer_product_name_c":"Sell Premier"},"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Sugar Serve","start_date_c":1653609600,"product_code_c":"SERVE","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"Ent","customer_product_name_c":"Sugar Serve"}},"emails":["cmoreno@sugarcrm.com"],"status":"disabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell Premier","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"IAPPS Test Customer","account_type":"Customer","date_entered":1653681521,"evaluation_c":0,"portal_users":0,"date_modified":1658343306,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"1c50bf13f778ce0f40393637d8054700","term_end_date_c":4102473600,"db_allocation_kb":70000000,"fs_allocation_kb":70000000,"term_start_date_c":1653609600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"on_premise_instance","account_partner_name":"","enforce_portal_users":1,"ms_nostorageemails_c":0,"producttemplate_id_c":"92ef9670-9b24-11ec-a8fe-02fb813964b8","account_managing_team":"Direct","ignore_expiration_date":0,"od_instance_location_c":"","storage_overage_profile":"consumption"}}',
                [
                    'SUGAR_SERVE' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1653609600,
                        'customer_product_name' => 'Sugar Serve',
                        'bundled_products' => [],
                    ],
                    'HINT' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1653609600,
                        'customer_product_name' => 'Sugar Hint',
                        'bundled_products' => [],
                    ],
                    'SUGAR_SELL_PREMIER_BUNDLE' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1653609600,
                        'customer_product_name' => 'Sell Premier',
                        'bundled_products' => [
                            'HINT' => [
                                'quantity' => 10,
                                'expiration_date' => 4102473600,
                                'start_date' => 1653609600,
                                'customer_product_name' => 'Sugar Hint',
                                'bundled_products' => [],
                            ],
                            'PREDICT_PREMIER' => [
                                'quantity' => 10,
                                'expiration_date' => 4102473600,
                                'start_date' => 1653609600,
                                'customer_product_name' => 'Sugar Predict',
                                'bundled_products' => [],
                            ],
                            'MAPS' => [
                                'quantity' => 10,
                                'expiration_date' => 4102473600,
                                'start_date' => 1653609600,
                                'customer_product_name' => '',
                                'bundled_products' => [],
                            ],
                        ],
                    ],
                    'MAPS' => [
                        'quantity' => 100,
                        'expiration_date' => 4102473600,
                        'start_date' => 1653609600,
                        'customer_product_name' => '',
                        'bundled_products' => [],
                    ],
                ],
            ],
            'SERVE Pluse with MAPS as a la carte and diff expiration date' => [
                '{"success":true,"error":"","subscription":{"id":"68670ce2-ddf7-11ec-9c26-027f079f78e1","addons":{"3b10614c-c18e-11ec-9d49-06eddc549468":{"quantity":10,"product_name":"Sugar Maps","start_date_c":1653609600,"product_code_c":"MAPS","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Maps"}, "23b54aaa-bd09-11ec-a5e7-029395602a62":{"quantity":10,"product_name":"Serve Add-On Package (for Sell Premier Customers)","start_date_c":1653609600,"product_code_c":"SERVE_ADDON_PACKAGE","expiration_date":4102473600,"bundled_products":{"3b10614c-c18e-11ec-9d49-06eddc549468":{"quantity":100,"product_name":"Sugar Maps","start_date_c":1653609600,"product_code_c":"MAPS","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"3e2e5147-257a-f562-8007-576ae9ab4d04":{"quantity":10,"product_name":"Customer Journey Plug-In","start_date_c":1653609600,"product_code_c":"","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1653609600,"product_code_c":"","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"bc19f754-3890-11eb-9074-02c10f456dba":{"quantity":10,"product_name":"Sugar Hint Applet","start_date_c":1653609600,"product_code_c":"HINT","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Hint"}},"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"92ef9670-9b24-11ec-a8fe-02fb813964b8":{"quantity":10,"product_name":"Sugar Sell Premier","start_date_c":1653609600,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"3b10614c-c18e-11ec-9d49-06eddc549468":{"quantity":10,"product_name":"Sugar Maps","start_date_c":1653609600,"product_code_c":"MAPS","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"3e2e5147-257a-f562-8007-576ae9ab4d04":{"quantity":10,"product_name":"Customer Journey Plug-In","start_date_c":1653609600,"product_code_c":"","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1653609600,"product_code_c":"","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"5604efdc-d92a-11ec-9875-06ef2d010d1d":{"quantity":10,"product_name":"Sugar Predict Premier","start_date_c":1653609600,"product_code_c":"PREDICT_PREMIER","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Predict"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1653609600,"product_code_c":"","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"bc19f754-3890-11eb-9074-02c10f456dba":{"quantity":10,"product_name":"Sugar Hint Applet","start_date_c":1653609600,"product_code_c":"HINT","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Hint"}},"product_edition_c":"SELL_PREMIER","deployment_flavor_c":"Ent","customer_product_name_c":"Sell Premier"},"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Sugar Serve","start_date_c":1653609600,"product_code_c":"SERVE","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"Ent","customer_product_name_c":"Sugar Serve"}},"emails":["cmoreno@sugarcrm.com"],"status":"disabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell Premier","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"IAPPS Test Customer","account_type":"Customer","date_entered":1653681521,"evaluation_c":0,"portal_users":0,"date_modified":1658343306,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"1c50bf13f778ce0f40393637d8054700","term_end_date_c":4102473600,"db_allocation_kb":70000000,"fs_allocation_kb":70000000,"term_start_date_c":1653609600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"on_premise_instance","account_partner_name":"","enforce_portal_users":1,"ms_nostorageemails_c":0,"producttemplate_id_c":"92ef9670-9b24-11ec-a8fe-02fb813964b8","account_managing_team":"Direct","ignore_expiration_date":0,"od_instance_location_c":"","storage_overage_profile":"consumption"}}',
                [
                    'SUGAR_SERVE' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1653609600,
                        'customer_product_name' => 'Sugar Serve',
                        'bundled_products' => [],
                    ],
                    'HINT' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1653609600,
                        'customer_product_name' => 'Sugar Hint',
                        'bundled_products' => [],
                    ],
                    'SUGAR_SELL_PREMIER_BUNDLE' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1653609600,
                        'customer_product_name' => 'Sell Premier',
                        'bundled_products' => [
                            'HINT' => [
                                'quantity' => 10,
                                'expiration_date' => 4102473600,
                                'start_date' => 1653609600,
                                'customer_product_name' => 'Sugar Hint',
                                'bundled_products' => [],
                            ],
                            'PREDICT_PREMIER' => [
                                'quantity' => 10,
                                'expiration_date' => 4102473600,
                                'start_date' => 1653609600,
                                'customer_product_name' => 'Sugar Predict',
                                'bundled_products' => [],
                            ],
                            'MAPS' => [
                                'quantity' => 10,
                                'expiration_date' => 4102473600,
                                'start_date' => 1653609600,
                                'customer_product_name' => '',
                                'bundled_products' => [],
                            ],
                        ],
                    ],
                    'MAPS' => [
                        'quantity' => 110,
                        'expiration_date' => 4102473600,
                        'start_date' => 1653609600,
                        'customer_product_name' => 'Sugar Maps',
                        'bundled_products' => [],
                    ],
                ],
            ],
            'SERVE Pluse with MAPS as a la carte and diff expiration date and diff custom_name' => [
                '{"success":true,"error":"","subscription":{"id":"68670ce2-ddf7-11ec-9c26-027f079f78e1","addons":{"3b10614c-c18e-11ec-9d49-06eddc549468":{"quantity":10,"product_name":"Sugar Maps","start_date_c":1653609600,"product_code_c":"MAPS","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Maps"}, "23b54aaa-bd09-11ec-a5e7-029395602a62":{"quantity":10,"product_name":"Serve Add-On Package (for Sell Premier Customers)","start_date_c":1653609600,"product_code_c":"SERVE_ADDON_PACKAGE","expiration_date":4102473600,"bundled_products":{"3b10614c-c18e-11ec-9d49-06eddc549468":{"quantity":100,"product_name":"Sugar Maps","start_date_c":1653609600,"product_code_c":"MAPS","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Maps for Serve Plus"},"3e2e5147-257a-f562-8007-576ae9ab4d04":{"quantity":10,"product_name":"Customer Journey Plug-In","start_date_c":1653609600,"product_code_c":"","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1653609600,"product_code_c":"","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"bc19f754-3890-11eb-9074-02c10f456dba":{"quantity":10,"product_name":"Sugar Hint Applet","start_date_c":1653609600,"product_code_c":"HINT","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Hint"}},"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"92ef9670-9b24-11ec-a8fe-02fb813964b8":{"quantity":10,"product_name":"Sugar Sell Premier","start_date_c":1653609600,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"3b10614c-c18e-11ec-9d49-06eddc549468":{"quantity":10,"product_name":"Sugar Maps","start_date_c":1653609600,"product_code_c":"MAPS","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"3e2e5147-257a-f562-8007-576ae9ab4d04":{"quantity":10,"product_name":"Customer Journey Plug-In","start_date_c":1653609600,"product_code_c":"","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1653609600,"product_code_c":"","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"5604efdc-d92a-11ec-9875-06ef2d010d1d":{"quantity":10,"product_name":"Sugar Predict Premier","start_date_c":1653609600,"product_code_c":"PREDICT_PREMIER","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Predict"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1653609600,"product_code_c":"","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":""},"bc19f754-3890-11eb-9074-02c10f456dba":{"quantity":10,"product_name":"Sugar Hint Applet","start_date_c":1653609600,"product_code_c":"HINT","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Hint"}},"product_edition_c":"SELL_PREMIER","deployment_flavor_c":"Ent","customer_product_name_c":"Sell Premier"},"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Sugar Serve","start_date_c":1653609600,"product_code_c":"SERVE","expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":"Ent","customer_product_name_c":"Sugar Serve"}},"emails":["cmoreno@sugarcrm.com"],"status":"disabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell Premier","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"IAPPS Test Customer","account_type":"Customer","date_entered":1653681521,"evaluation_c":0,"portal_users":0,"date_modified":1658343306,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"1c50bf13f778ce0f40393637d8054700","term_end_date_c":4102473600,"db_allocation_kb":70000000,"fs_allocation_kb":70000000,"term_start_date_c":1653609600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"on_premise_instance","account_partner_name":"","enforce_portal_users":1,"ms_nostorageemails_c":0,"producttemplate_id_c":"92ef9670-9b24-11ec-a8fe-02fb813964b8","account_managing_team":"Direct","ignore_expiration_date":0,"od_instance_location_c":"","storage_overage_profile":"consumption"}}',
                [
                    'SUGAR_SERVE' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1653609600,
                        'customer_product_name' => 'Sugar Serve',
                        'bundled_products' => [],
                    ],
                    'HINT' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1653609600,
                        'customer_product_name' => 'Sugar Hint',
                        'bundled_products' => [],
                    ],
                    'SUGAR_SELL_PREMIER_BUNDLE' => [
                        'quantity' => 10,
                        'expiration_date' => 4102473600,
                        'start_date' => 1653609600,
                        'customer_product_name' => 'Sell Premier',
                        'bundled_products' => [
                            'HINT' => [
                                'quantity' => 10,
                                'expiration_date' => 4102473600,
                                'start_date' => 1653609600,
                                'customer_product_name' => 'Sugar Hint',
                                'bundled_products' => [],
                            ],
                            'PREDICT_PREMIER' => [
                                'quantity' => 10,
                                'expiration_date' => 4102473600,
                                'start_date' => 1653609600,
                                'customer_product_name' => 'Sugar Predict',
                                'bundled_products' => [],
                            ],
                            'MAPS' => [
                                'quantity' => 10,
                                'expiration_date' => 4102473600,
                                'start_date' => 1653609600,
                                'customer_product_name' => '',
                                'bundled_products' => [],
                            ],
                        ],
                    ],
                    'MAPS' => [
                        'quantity' => 110,
                        'expiration_date' => 4102473600,
                        'start_date' => 1653609600,
                        'customer_product_name' => 'Sugar Maps',
                        'bundled_products' => [],
                    ],
                ],
            ],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * @covers ::getSubscriptionKeys
     * @covers ::getTopLevelSubscriptionKeys
     * @covers ::getAllSubscriptionKeys
     * @covers ::getAddonProducts
     *
     * @dataProvider getSubscriptionKeysProvider
     */
    public function testGetSubscriptionKeys($data, $getAll, $expected)
    {
        $subscription = new Subscription($data);
        if ($getAll) {
            $this->assertSame($expected, $subscription->getAllSubscriptionKeys());
        } else {
            $this->assertSame($expected, $subscription->getTopLevelSubscriptionKeys());
        }
    }

    public function getSubscriptionKeysProvider()
    {
        // @codingStandardsIgnoreStart
        return [
            'SERVE only' => [
                '{"success":true,"error":"","subscription":{"id":"ffffc6a2-6ac3-11e9-b0f5-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597765,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"47fa5aa6620415261cd7bcd2a8de6d31","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                true,
                [
                    'SUGAR_SERVE' => true,
                ],
            ],
            'no subscription section' => [
                '{"no subscription section": {"quantity" : "100"}}',
                true,
                [],
            ],
            'ENT + SERVE' => [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":100,"product_name":"Sugar Enterprise","start_date_c":1556175600,"product_code_c":"ENT","expiration_date":4102473600,"deployment_flavor_c":"Ent"}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                true,
                [
                    'CURRENT' => true,
                    'SUGAR_SERVE' => true,
                ],
            ],
            'SELL + SURVE' => [
                '{"success":true,"error":"","subscription":{"id":"7387f7e2-7b50-11e9-9e70-02c10f456dba","debug":0,"addons":{"181aee1c-7b3e-11e9-b962-02c10f456dba":{"quantity":10,"product_name":"Sugar Sell (DEV ONLY)","start_date_c":1558335600,"product_code_c":"SELL","expiration_date":4102473600,"deployment_flavor_c":""},"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1558335600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1558417307,"evaluation_c":0,"portal_users":0,"date_modified":1558487606,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"944a2c9714859bed45493f69a95e6999","term_end_date_c":4102473600,"term_start_date_c":1558335600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                false,
                [
                    'SUGAR_SELL' => true,
                    'SUGAR_SERVE' => true,
                ],
            ],
            'SELL only' => [
                '{"success":true,"error":"","subscription":{"id":"3efc5dc4-7b50-11e9-9f42-02c10f456dba","debug":0,"addons":{"181aee1c-7b3e-11e9-b962-02c10f456aaa":{"quantity":10,"product_name":"Sugar Sell (DEV ONLY)","start_date_c":1558335600,"product_code_c":"SELL","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1558417219,"evaluation_c":0,"portal_users":0,"date_modified":1558487575,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"3779135395d186056bbcc895dc3cfc00","term_end_date_c":4102473600,"term_start_date_c":1558335600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                true,
                [
                    'SUGAR_SELL' => true,
                ],
            ],
            'SERVE + SELL + ENT' => [
                '{"success":true,"error":"","subscription":{"id":"68ad7ebd-d522-67e2-6aea-570fe9baf420","debug":0,"addons":{"11d7e3f8-ed89-f588-e9af-4dbf44a9b207":{"quantity":"150","product_name":"iPad with offline sync","start_date_c":1460617200,"product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":""},"181aee1c-7b3e-11e9-b962-02c10f456dba":{"quantity":150,"product_name":"Sugar Sell","start_date_c":1563174000,"product_code_c":"SELL","expiration_date":4102473600,"deployment_flavor_c":"Ent"},"37f53940-8ca0-e49a-5b11-4dbf4499a788":{"quantity":"150","product_name":"Blackberry with offline sync","start_date_c":1460617200,"product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":""},"4052c256-ab6c-6111-b6f8-4dbf44ae8408":{"quantity":150,"product_name":"Sugar Plug-in for Lotus Notes","start_date_c":"","product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":"Ent"},"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":150,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":"","product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":""},"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":150,"product_name":"Sugar Serve","start_date_c":1563174000,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":"Ent"},"b0fade74-2556-d181-83c7-4dbf44ee21fa":{"quantity":"150","product_name":"iPhone with offline sync","start_date_c":1460617200,"product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":150,"product_name":"Sugar Enterprise","start_date_c":1460617200,"product_code_c":"ENT","expiration_date":4102473600,"deployment_flavor_c":"Ent"}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"e6e4d734-ce3c-2163-b218-4942c7410ef0","quantity_c":"150","account_name":"SugarCRM Partner Portal Login","account_type":"Partner","date_entered":1460685883,"evaluation_c":0,"portal_users":150,"date_modified":1566439202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"4ba82b21756db68afbcdcc76214ec577","term_end_date_c":4102473600,"term_start_date_c":1460617200,"account_partner_id":"","enforce_user_limit":0,"od_instance_name_c":"qatest","account_partner_name":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                false,
                [
                    'CURRENT' => true,
                    'SUGAR_SELL' => true,
                    'SUGAR_SERVE' => true,
                ],
            ],
            'SELL Bundle' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":10,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":1639958400,"product_code_nc":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Connector for LinkedIn Sales Navigator"},"product-template-id-for-sell-premiere":{"quantity":10,"product_name":"Sell Premiere","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1639958400,"product_code_c":"DISCOVER","expiration_date":"","product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Discover"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1639958400,"product_code_c":"CONNECT","expiration_date":"","product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Connect"}},"product_edition_c":"SELL_PREMIER","deployment_flavor_c":"Ent","customer_product_name_c":"Sell Premiere"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell Premiere","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-premiere","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                false,
                [
                    'SUGAR_SELL_PREMIER_BUNDLE' => true,
                ]
            ],
            'SELL Premier Bundle with all levels' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":10,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":1639958400,"product_code_nc":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Connector for LinkedIn Sales Navigator"},"product-template-id-for-sell-premiere":{"quantity":10,"product_name":"Sell Premiere","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1639958400,"product_code_c":"DISCOVER","expiration_date":"","product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Discover"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1639958400,"product_code_c":"CONNECT","expiration_date":"","product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Connect"}},"product_edition_c":"SELL_PREMIER","deployment_flavor_c":"Ent","customer_product_name_c":"Sell Premiere"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell Premiere","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-premiere","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                true,
                [
                    'SUGAR_SELL_PREMIER_BUNDLE' => true,
                    'CONNECT' => true,
                    'DISCOVER' => true,
                ],
            ],
            'SELL Advanced Bundle with all levels' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":10,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":1639958400,"product_code_nc":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Connector for LinkedIn Sales Navigator"},"product-template-id-for-sell-premiere":{"quantity":10,"product_name":"Sell essentials","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1639958400,"product_code_c":"DISCOVER","expiration_date":"","product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Discover"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1639958400,"product_code_c":"CONNECT","expiration_date":"","product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Connect"}},"product_edition_c":"SELL_ADVANCED","deployment_flavor_c":"Ent","customer_product_name_c":"Sell Premiere"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell Premiere","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-premiere","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                true,
                [
                    'SUGAR_SELL_ADVANCED_BUNDLE' => true,
                    'CONNECT' => true,
                    'DISCOVER' => true,
                ],
            ],
            'SELL Essentials with all levels' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":10,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":1639958400,"product_code_nc":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Connector for LinkedIn Sales Navigator"},"product-template-id-for-sell-premiere":{"quantity":10,"product_name":"Sell essentials","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1639958400,"product_code_c":"DISCOVER","expiration_date":"","product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Discover"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1639958400,"product_code_c":"CONNECT","expiration_date":"","product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Connect"}},"product_edition_c":"SELL_ESSENTIALS","deployment_flavor_c":"Ent","customer_product_name_c":"Sell Premiere"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell Premiere","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-premiere","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                true,
                [
                    'SUGAR_SELL_ESSENTIALS' => true,
                    'CONNECT' => true,
                    'DISCOVER' => true,
                ],
            ],
            'SELL Eassential with all levels, but no bundled products' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"product-template-id-for-sell-premiere":{"quantity":10,"product_name":"Sell essentials","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600,"product_edition_c":"SELL_ESSENTIALS","deployment_flavor_c":"Ent","customer_product_name_c":"Sell Premiere"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell Premiere","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-premiere","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                true,
                [
                    'SUGAR_SELL_ESSENTIALS' => true,
                ],
            ],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * @covers ::getSubscriptionByKey
     * @covers ::getAddonProducts
     *
     * @dataProvider getSubscriptionByKeyProvider
     */
    public function testGetSubscriptionByKey($data, $key, $expected)
    {
        $subscription = new Subscription($data);
        $this->assertSame($expected, $subscription->getSubscriptionByKey($key));
    }

    public function getSubscriptionByKeyProvider()
    {
        // @codingStandardsIgnoreStart
        return [
            'SERVE only' => [
                '{"success":true,"error":"","subscription":{"id":"ffffc6a2-6ac3-11e9-b0f5-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597765,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"47fa5aa6620415261cd7bcd2a8de6d31","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                'SUGAR_SERVE',
                [
                    'quantity' => 10,
                    'expiration_date' => 4102473600,
                    'start_date' => 1556175600,
                    'customer_product_name' => null,
                    'bundled_products' => [],
                ],
            ],
            'SERVE only key is SELL' => [
                '{"success":true,"error":"","subscription":{"id":"ffffc6a2-6ac3-11e9-b0f5-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597765,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"47fa5aa6620415261cd7bcd2a8de6d31","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                'SUGAR_SELL',
                [],
            ],
            'no subscription section' => [
                '{"no subscription section": {"quantity" : "100"}}',
                'SUGAR_SERVE',
                [],
            ],
            'SELL + SURVE, key = SELL' => [
                '{"success":true,"error":"","subscription":{"id":"7387f7e2-7b50-11e9-9e70-02c10f456dba","debug":0,"addons":{"181aee1c-7b3e-11e9-b962-02c10f456dba":{"quantity":110,"product_name":"Sugar Sell (DEV ONLY)","start_date_c":1558335600,"product_code_c":"SELL","expiration_date":4102473600,"deployment_flavor_c":""},"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1558335600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1558417307,"evaluation_c":0,"portal_users":0,"date_modified":1558487606,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"944a2c9714859bed45493f69a95e6999","term_end_date_c":4102473600,"term_start_date_c":1558335600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                'SUGAR_SELL',
                [
                    'quantity' => 110,
                    'expiration_date' => 4102473600,
                    'start_date' => 1558335600,
                    'customer_product_name' => null,
                    'bundled_products' => [],
                ],
            ],
            'SELL + SURVE and key = SERVE' => [
                '{"success":true,"error":"","subscription":{"id":"7387f7e2-7b50-11e9-9e70-02c10f456dba","debug":0,"addons":{"181aee1c-7b3e-11e9-b962-02c10f456dba":{"quantity":100,"product_name":"Sugar Sell (DEV ONLY)","start_date_c":1558335611,"product_code_c":"SELL","expiration_date":4102473600,"deployment_flavor_c":""},"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":1110,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1558335600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1558417307,"evaluation_c":0,"portal_users":0,"date_modified":1558487606,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"944a2c9714859bed45493f69a95e6999","term_end_date_c":4102473600,"term_start_date_c":1558335600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                'SUGAR_SERVE',
                [
                    'quantity' => 1110,
                    'expiration_date' => 4102473600,
                    'start_date' => 1558335600,
                    'customer_product_name' => null,
                    'bundled_products' => [],
                ],
            ],
            'SELL Bundle and key = SUGAR_SELL_PREMIER_BUNDLE ' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":10,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":1639958400,"product_code_nc":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Connector for LinkedIn Sales Navigator"},"product-template-id-for-sell-premiere":{"quantity":10,"product_name":"Sell Premiere","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1639958400,"product_code_c":"DISCOVER","expiration_date":"","product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Discover"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1639958400,"product_code_c":"CONNECT","expiration_date":"","product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Connect"}},"product_edition_c":"SELL_PREMIER","deployment_flavor_c":"Ent","customer_product_name_c":"Sell Premiere"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell Premiere","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-premiere","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                'SUGAR_SELL_PREMIER_BUNDLE',
                [
                    'quantity' => 10,
                    'expiration_date' => 4102473600,
                    'start_date' => 1639958400,
                    'customer_product_name' => 'Sell Premiere',
                    'bundled_products' => [
                        'CONNECT' => [
                            'quantity' => 10,
                            'expiration_date' => 4102473600,
                            'start_date' => 1639958400,
                            'customer_product_name' => 'Sugar Connect',
                            'bundled_products' => [],
                        ],
                        'DISCOVER' => [
                            'quantity' => 10,
                            'expiration_date' => 4102473600,
                            'start_date' => 1639958400,
                            'customer_product_name' => 'Sugar Discover',
                            'bundled_products' => [],
                        ],
                    ],
                ],
            ],
            'SELL Bundle key is bundled key DISCOVER' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":10,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":1639958400,"product_code_nc":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Connector for LinkedIn Sales Navigator"},"product-template-id-for-sell-premiere":{"quantity":10,"product_name":"Sell Premiere","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1639958400,"product_code_c":"DISCOVER","expiration_date":"","product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Discover"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1639958400,"product_code_c":"CONNECT","expiration_date":"","product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Connect"}},"product_edition_c":"SELL_PREMIER","deployment_flavor_c":"Ent","customer_product_name_c":"Sell Premiere"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell Premiere","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-premiere","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                Subscription::SUGAR_DISCOVER_KEY,
                [
                    'quantity' => 10,
                    'expiration_date' => 4102473600,
                    'start_date' => 1639958400,
                    'customer_product_name' => 'Sugar Discover',
                    'bundled_products' => [],
                ],
            ]
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     *
     * @covers ::getAddonProducts
     * @param string $key
     * @param bool $expected
     *
     * @dataProvider getAddonProductsProvider
     */
    public function testGetAddonProducts(string $key, bool $expected)
    {
        $subscriptionMock = $this->getMockBuilder(Subscription::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();

        $this->assertSame($expected, in_array($key, $subscriptionMock->getAddonProducts()));
    }

    public function getAddonProductsProvider()
    {
        return [
            'Ent/pro/Ult is not an addon key' => [Subscription::SUGAR_BASIC_KEY, false],
            'Sell is addon key' => [Subscription::SUGAR_SELL_KEY, true],
            'Serve is addon key' => [Subscription::SUGAR_SERVE_KEY, true],
            'Hint is addon key' => [Subscription::SUGAR_HINT_KEY, true],
            'Sell bundle is addon key' => [Subscription::SUGAR_SELL_BUNDLE_KEY, true],
            'Sell essentials is addon key' => [Subscription::SUGAR_SELL_ESSENTIALS_KEY, true],
            'Sell advanced is addon key' => [Subscription::SUGAR_SELL_ADVANCED_BUNDLE_KEY, true],
            'Sell premier bundle is addon key' => [Subscription::SUGAR_SELL_PREMIER_BUNDLE_KEY, true],
            'Other keys are not addon key' => ['otherKey', false],
        ];
    }

    /**
     * @covers ::isMangoKey
     * @param string $key
     * @param $expected
     *
     * @dataProvider isMangoKeyProvider
     */
    public function testIsMangoKey(?string $key, $expected)
    {
        $this->assertSame($expected, Subscription::isMangoKey($key));
    }

    public function isMangoKeyProvider()
    {
        return [
            'Ent is Mango keys' => [Subscription::SUGAR_BASIC_KEY, true],
            'Sell is a Mango key' => [Subscription::SUGAR_SELL_KEY, true],
            'Serve is a Mango key' => [Subscription::SUGAR_SERVE_KEY, true],
            'Hint is not a Mango key' => [Subscription::SUGAR_HINT_KEY, false],
            'Sell bundles is a mango key' => [Subscription::SUGAR_SELL_BUNDLE_KEY, true],
            'Sell esstentials is a mango key' => [Subscription::SUGAR_SELL_ESSENTIALS_KEY, true],
            'Sell Premier is a mango key' => [Subscription::SUGAR_SELL_PREMIER_BUNDLE_KEY, true],
            'Predict advanced is not a Mango key' => [Subscription::SUGAR_PREDICT_ADVANCED_KEY, false],
            'Predict premier is not a Mango key' => [Subscription::SUGAR_PREDICT_PREMIER_KEY, false],
            'Connect is not a Mango key' => [Subscription::SUGAR_CONNECT_KEY, false],
            'Discovery is not a Mango key' => [Subscription::SUGAR_DISCOVER_KEY, false],
            'Ad forecast is not a Mango key' => [Subscription::SUGAR_ADVANCEDFORECAST_KEY, false],
            'WMaps is not a Mango key' => [Subscription::SUGAR_MAPS_KEY, false],
            'Other keys are not Mango key' => ['otherKey', false],
            'empty is not a Mango key' => [null, false],
        ];
    }

    /**
     * @covers ::getSellKey
     * @param string $key
     * @param $expected
     *
     * @dataProvider GetSellyProvider
     */
    public function testGetSellKey($licenseTypes, $expected)
    {
        $this->assertSame($expected, Subscription::getSellKey($licenseTypes));
    }

    public function GetSellyProvider()
    {
        return [
            'Ent keys' => [
                [Subscription::SUGAR_BASIC_KEY],
                '',
            ],
            'empty license key' => [
                [],
                '',
            ],
            'Sell' => [
                [Subscription::SUGAR_BASIC_KEY, Subscription::SUGAR_SELL_KEY],
                Subscription::SUGAR_SELL_KEY,
            ],
            'Sell esstentials' => [
                [Subscription::SUGAR_SERVE_KEY, Subscription::SUGAR_SELL_ESSENTIALS_KEY],
                Subscription::SUGAR_SELL_ESSENTIALS_KEY,
            ],
            'Sell Premier' => [
                [Subscription::SUGAR_SERVE_KEY, Subscription::SUGAR_SELL_PREMIER_BUNDLE_KEY],
                Subscription::SUGAR_SELL_PREMIER_BUNDLE_KEY,
            ],
        ];
    }

    /**
     * @covers ::getOrderedLicenseTypes
     * @param string $key
     * @param $expected
     *
     * @dataProvider getOrderedLicenseTypesProvider
     */
    public function testGetOrderedLicenseTypes(?array $LicenseTypes, array $expected)
    {
        $this->assertSame($expected, Subscription::getOrderedLicenseTypes($LicenseTypes));
    }

    public function getOrderedLicenseTypesProvider()
    {
        return [
            'Mango keys mixed' => [
                [
                    Subscription::SUGAR_HINT_KEY,
                    Subscription::SUGAR_BASIC_KEY,
                    Subscription::SUGAR_SELL_PREMIER_BUNDLE_KEY,
                    Subscription::SUGAR_SELL_KEY,
                ],
                [
                    Subscription::SUGAR_SELL_PREMIER_BUNDLE_KEY,
                    Subscription::SUGAR_SELL_KEY,
                    Subscription::SUGAR_BASIC_KEY,
                    Subscription::SUGAR_HINT_KEY,
                ],
            ],
            'hiden keys removed, non-crm ordered' => [
                [
                    Subscription::SUGAR_MAPS_KEY,
                    Subscription::SUGAR_HINT_KEY,
                    Subscription::SUGAR_CONNECT_KEY,
                    Subscription::SUGAR_ADVANCEDFORECAST_KEY,
                    Subscription::SUGAR_DISCOVER_KEY,
                    Subscription::SUGAR_PREDICT_ADVANCED_KEY,
                    Subscription::SUGAR_PREDICT_ADVANCED_KEY,
                    Subscription::SUGAR_BASIC_KEY,
                    Subscription::SUGAR_SELL_PREMIER_BUNDLE_KEY,
                    Subscription::SUGAR_SELL_KEY,
                ],
                [
                    Subscription::SUGAR_SELL_PREMIER_BUNDLE_KEY,
                    Subscription::SUGAR_SELL_KEY,
                    Subscription::SUGAR_BASIC_KEY,
                    Subscription::SUGAR_HINT_KEY,
                    Subscription::SUGAR_MAPS_KEY,
                ],
            ],
            'empty key' => [
                null,
                [],
            ],
        ];
    }
}
