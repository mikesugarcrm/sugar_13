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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Entitlements\SubscriptionManager;
use Sugarcrm\Sugarcrm\Entitlements\Subscription;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class SubscriptionManagerTest
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Entitlements\SubscriptionManager
 */
class SubscriptionManagerTest extends TestCase
{
    /**
     * @covers ::getUserSubscriptions
     * @covers ::getAllUserSubscriptions
     * @covers ::getTopLevelUserSubscriptions
     * @covers ::getSystemSubscriptions
     * @covers ::getSubscription
     * @covers ::instance
     * @covers ::getSystemSubscriptionKeys
     * @covers ::getAllSystemSubscriptionKeys
     * @covers ::getTopLevelSystemSubscriptionKeys
     * @covers ::getUserDefaultLicenseType
     * @covers ::getAllSupportedProducts
     * @covers ::getSystemSubscriptionByKey
     *
     * @dataProvider getUserSubscriptionsProvider
     */
    public function testGetUserSubscriptions($data, $userLicenseType, $licenseKey, $isAdmin, $getAll, $expected)
    {
        $userMock = $this->getMockBuilder(\User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLicenseTypes'])
            ->getMock();

        $userMock->expects($this->any())
            ->method('getLicenseTypes')
            ->will($this->returnValue($userLicenseType));

        $userMock->is_admin = $isAdmin;

        $subMock = $this->getMockBuilder(SubscriptionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSubscription', 'getLicenseKey'])
            ->getMock();

        $subMock->expects($this->any())
            ->method('getSubscription')
            ->will($this->returnValue(new Subscription($data)));

        $subMock->expects($this->any())
            ->method('getLicenseKey')
            ->will($this->returnValue($licenseKey));

        if ($getAll) {
            $this->assertSame($expected, $subMock->getAllUserSubscriptions($userMock));
        } else {
            $this->assertSame($expected, $subMock->getTopLevelUserSubscriptions($userMock));
        }
    }

    public function getUserSubscriptionsProvider()
    {
        // @codingStandardsIgnoreStart
        return [
            'user\'s license Type is empty, product is SERVE + ENT' => [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":100,"product_name":"Sugar Enterprise","start_date_c":1556175600,"product_code_c":"ENT","expiration_date":4102473600,"deployment_flavor_c":"Ent"}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',

                [],
                'any_key',
                true,
                true,
                ['CURRENT'],
            ],
            'user\'s license Type is SERVE, product is SERVE' => [
                '{"success":true,"error":"","subscription":{"id":"ffffc6a2-6ac3-11e9-b0f5-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597765,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"47fa5aa6620415261cd7bcd2a8de6d31","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                ['SUGAR_SERVE'],
                'any_key',
                false,
                true,
                ['SUGAR_SERVE'],
            ],
            'user\'s license Type is SERVE, product is SERVE + ENT' => [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":100,"product_name":"Sugar Enterprise","start_date_c":1556175600,"product_code_c":"ENT","expiration_date":4102473600,"deployment_flavor_c":"Ent"}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',

                ['SUGAR_SERVE'],
                'any_key',
                false,
                true,
                ['SUGAR_SERVE'],
            ],
            'user\'s license Type is CURRENT, product is ENT only' => [
                '{"success":true,"error":"","subscription":{"id":"914f07ac-3acb-3a3a-8d4f-570fe8dcae78","debug":0,"addons":{"11d7e3f8-ed89-f588-e9af-4dbf44a9b207":{"quantity":"150","product_name":"iPad with offline sync","start_date_c":"","expiration_date":4102473600,"product_code_c":""},"37f53940-8ca0-e49a-5b11-4dbf4499a788":{"quantity":"150","product_name":"Blackberry with offline sync","start_date_c":"","expiration_date":4102473600,"product_code_c":""},"4052c256-ab6c-6111-b6f8-4dbf44ae8408":{"quantity":"150","product_name":"Sugar Plug-in for Lotus Notes","start_date_c":"","expiration_date":4102473600,"product_code_c":"Ent"},"b0fade74-2556-d181-83c7-4dbf44ee21fa":{"quantity":"150","product_name":"iPhone with offline sync","start_date_c":"","expiration_date":4102473600,"product_code_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":150,"product_name":"Sugar Enterprise","start_date_c":1460617200,"expiration_date":4102473600,"product_code_c":"Ent"}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"e6e4d734-ce3c-2163-b218-4942c7410ef0","account_name":"SugarCRM Partner Portal Login","account_type":"Partner","date_entered":1460685667,"evaluation_c":0,"portal_users":150,"date_modified":1558836002,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"5fd99624e58ec184c96d0520d9ab8b2d","term_end_date_c":4102473600,"term_start_date_c":1460617200,"enforce_user_limit":0,"od_instance_name_c":"qatest","enforce_portal_users":0,"ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                ['CURRENT'],
                'any_key',
                false,
                true,
                ['CURRENT'],
            ],
            'user\'s license Type is SERVE, product is ENT only' => [
                '{"success":true,"error":"","subscription":{"id":"914f07ac-3acb-3a3a-8d4f-570fe8dcae78","debug":0,"addons":{"11d7e3f8-ed89-f588-e9af-4dbf44a9b207":{"quantity":"150","product_name":"iPad with offline sync","start_date_c":"","expiration_date":4102473600,"product_code_c":""},"37f53940-8ca0-e49a-5b11-4dbf4499a788":{"quantity":"150","product_name":"Blackberry with offline sync","start_date_c":"","expiration_date":4102473600,"product_code_c":""},"4052c256-ab6c-6111-b6f8-4dbf44ae8408":{"quantity":"150","product_name":"Sugar Plug-in for Lotus Notes","start_date_c":"","expiration_date":4102473600,"product_code_c":"Ent"},"b0fade74-2556-d181-83c7-4dbf44ee21fa":{"quantity":"150","product_name":"iPhone with offline sync","start_date_c":"","expiration_date":4102473600,"product_code_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":150,"product_name":"Sugar Enterprise","start_date_c":1460617200,"expiration_date":4102473600,"product_code_c":"Ent"}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"e6e4d734-ce3c-2163-b218-4942c7410ef0","account_name":"SugarCRM Partner Portal Login","account_type":"Partner","date_entered":1460685667,"evaluation_c":0,"portal_users":150,"date_modified":1558836002,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"5fd99624e58ec184c96d0520d9ab8b2d","term_end_date_c":4102473600,"term_start_date_c":1460617200,"enforce_user_limit":0,"od_instance_name_c":"qatest","enforce_portal_users":0,"ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                ['SUGAR_SERVE'],
                'any_key',
                false,
                true,
                [],
            ],
            'user is admin, license Type is SERVE, product is ENT only' => [
                '{"success":true,"error":"","subscription":{"id":"914f07ac-3acb-3a3a-8d4f-570fe8dcae78","debug":0,"addons":{"11d7e3f8-ed89-f588-e9af-4dbf44a9b207":{"quantity":"150","product_name":"iPad with offline sync","start_date_c":"","expiration_date":4102473600,"product_code_c":""},"37f53940-8ca0-e49a-5b11-4dbf4499a788":{"quantity":"150","product_name":"Blackberry with offline sync","start_date_c":"","expiration_date":4102473600,"product_code_c":""},"4052c256-ab6c-6111-b6f8-4dbf44ae8408":{"quantity":"150","product_name":"Sugar Plug-in for Lotus Notes","start_date_c":"","expiration_date":4102473600,"product_code_c":"Ent"},"b0fade74-2556-d181-83c7-4dbf44ee21fa":{"quantity":"150","product_name":"iPhone with offline sync","start_date_c":"","expiration_date":4102473600,"product_code_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":150,"product_name":"Sugar Enterprise","start_date_c":1460617200,"expiration_date":4102473600,"product_code_c":"Ent"}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"e6e4d734-ce3c-2163-b218-4942c7410ef0","account_name":"SugarCRM Partner Portal Login","account_type":"Partner","date_entered":1460685667,"evaluation_c":0,"portal_users":150,"date_modified":1558836002,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"5fd99624e58ec184c96d0520d9ab8b2d","term_end_date_c":4102473600,"term_start_date_c":1460617200,"enforce_user_limit":0,"od_instance_name_c":"qatest","enforce_portal_users":0,"ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                ['SUGAR_SERVE'],
                'any_key',
                true,
                true,
                ['CURRENT'],
            ],
            'user\'s license Type is CURRENT, product is SERVE' => [
                '{"success":true,"error":"","subscription":{"id":"ffffc6a2-6ac3-11e9-b0f5-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597765,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"47fa5aa6620415261cd7bcd2a8de6d31","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                ['CURRENT'],
                'any_key',
                false,
                true,
                ['SUGAR_SERVE'],
            ],
            'no subscription' => [
                '{"no subscription section": {"quantity" : "100"}}',
                ['CURRENT'],
                'any_key',
                false,
                true,
                [],
            ],
            'user\'s license Type is SELL, product is SERVE' => [
                '{"success":true,"error":"","subscription":{"id":"ffffc6a2-6ac3-11e9-b0f5-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597765,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"47fa5aa6620415261cd7bcd2a8de6d31","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                ['SUGAR_SELL'],
                'any_key',
                false,
                false,
                [],
            ],
            'user is admin, license Type is SELL, product is SERVE' => [
                '{"success":true,"error":"","subscription":{"id":"ffffc6a2-6ac3-11e9-b0f5-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597765,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"47fa5aa6620415261cd7bcd2a8de6d31","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                ['SUGAR_SELL'],
                'any_key',
                true,
                false,
                ['SUGAR_SERVE'],
            ],
            'user\'s license Type is SELL, product is SERVE + ENT' => [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":100,"product_name":"Sugar Enterprise","start_date_c":1556175600,"product_code_c":"ENT","expiration_date":4102473600,"deployment_flavor_c":"Ent"}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                ['SUGAR_SELL'],
                'any_key',
                false,
                false,
                [],
            ],
            'user\'s license Type is SERVE + ENT, product is SERVE + ENT' => [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":100,"product_name":"Sugar Enterprise","start_date_c":1556175600,"product_code_c":"ENT","expiration_date":4102473600,"deployment_flavor_c":"Ent"}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                ['SUGAR_SERVE', 'CURRENT'],
                'any_key',
                false,
                false,
                ['CURRENT', 'SUGAR_SERVE'],
            ],
            'user\'s license Type is empty, product is SERVE' => [
                '{"success":true,"error":"","subscription":{"id":"ffffc6a2-6ac3-11e9-b0f5-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597765,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"47fa5aa6620415261cd7bcd2a8de6d31","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [],
                'any_key',
                false,
                true,
                ['SUGAR_SERVE'],
            ],
            'user\'s license Type is empty, product is SERVE + SELL + ENT' => [
                '{"success":true,"error":"","subscription":{"id":"68ad7ebd-d522-67e2-6aea-570fe9baf420","debug":0,"addons":{"11d7e3f8-ed89-f588-e9af-4dbf44a9b207":{"quantity":"150","product_name":"iPad with offline sync","start_date_c":1460617200,"product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":""},"181aee1c-7b3e-11e9-b962-02c10f456dba":{"quantity":150,"product_name":"Sugar Sell","start_date_c":1563174000,"product_code_c":"SELL","expiration_date":4102473600,"deployment_flavor_c":"Ent"},"37f53940-8ca0-e49a-5b11-4dbf4499a788":{"quantity":"150","product_name":"Blackberry with offline sync","start_date_c":1460617200,"product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":""},"4052c256-ab6c-6111-b6f8-4dbf44ae8408":{"quantity":150,"product_name":"Sugar Plug-in for Lotus Notes","start_date_c":"","product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":"Ent"},"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":150,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":"","product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":""},"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":150,"product_name":"Sugar Serve","start_date_c":1563174000,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":"Ent"},"b0fade74-2556-d181-83c7-4dbf44ee21fa":{"quantity":"150","product_name":"iPhone with offline sync","start_date_c":1460617200,"product_code_c":"","expiration_date":4102473600,"deployment_flavor_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":150,"product_name":"Sugar Enterprise","start_date_c":1460617200,"product_code_c":"ENT","expiration_date":4102473600,"deployment_flavor_c":"Ent"}},"emails":[],"status":"enabled","audited":1,"domains":[],"product":"ENT","perpetual":0,"account_id":"e6e4d734-ce3c-2163-b218-4942c7410ef0","quantity_c":"150","account_name":"SugarCRM Partner Portal Login","account_type":"Partner","date_entered":1460685883,"evaluation_c":0,"portal_users":150,"date_modified":1566439202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"4ba82b21756db68afbcdcc76214ec577","term_end_date_c":4102473600,"term_start_date_c":1460617200,"account_partner_id":"","enforce_user_limit":0,"od_instance_name_c":"qatest","account_partner_name":"","enforce_portal_users":0,"producttemplate_id_c":"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [],
                'any_key',
                true,
                true,
                ['CURRENT'],
            ],
            'user\'s license Type is empty, product is SERVE + SELL' => [
                '{"success":true,"error":"","subscription":{"id":"7387f7e2-7b50-11e9-9e70-02c10f456dba","debug":0,"addons":{"181aee1c-7b3e-11e9-b962-02c10f456dba":{"quantity":10,"product_name":"Sugar Sell (DEV ONLY)","start_date_c":1558335600,"product_code_c":"SELL","expiration_date":4102473600,"deployment_flavor_c":""},"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1558335600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1558417307,"evaluation_c":0,"portal_users":0,"date_modified":1558487606,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"944a2c9714859bed45493f69a95e6999","term_end_date_c":4102473600,"term_start_date_c":1558335600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [],
                'any_key',
                true,
                true,
                ['SUGAR_SERVE'],
            ],
            'user\'s license Type is empty, product is SERVE + SELL, license key is empty' => [
                '{"success":true,"error":"","subscription":{"id":"7387f7e2-7b50-11e9-9e70-02c10f456dba","debug":0,"addons":{"181aee1c-7b3e-11e9-b962-02c10f456dba":{"quantity":10,"product_name":"Sugar Sell (DEV ONLY)","start_date_c":1558335600,"product_code_c":"SELL","expiration_date":4102473600,"deployment_flavor_c":""},"aa8834fa-6ac0-11e9-b588-02c10f456dba":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1558335600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1558417307,"evaluation_c":0,"portal_users":0,"date_modified":1558487606,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"944a2c9714859bed45493f69a95e6999","term_end_date_c":4102473600,"term_start_date_c":1558335600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                [],
                null,
                true,
                true,
                [],
            ],
            'user\'s license type is empty, bundle subscription' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":10,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":1639958400,"product_code_nc":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Connector for LinkedIn Sales Navigator"},"product-template-id-for-sell-PREMIER":{"quantity":10,"product_name":"Sell PREMIER","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1639958400,"product_code_c":"DISCOVER","expiration_date":"","product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Discover"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1639958400,"product_code_c":"CONNECT","expiration_date":"","product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Connect"}},"product_edition_c":"SELL_PREMIERE","deployment_flavor_c":"Ent","customer_product_name_c":"Sell PREMIER"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell PREMIER","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-PREMIER","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                [],
                'any_key',
                true,
                false,
                ['SUGAR_SELL_BUNDLE'],
            ],
            'user\'s license type is SELL Bundle' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":10,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":1639958400,"product_code_nc":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Connector for LinkedIn Sales Navigator"},"product-template-id-for-sell-PREMIER":{"quantity":10,"product_name":"Sell PREMIER","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1639958400,"product_code_c":"DISCOVER","expiration_date":"","product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Discover"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1639958400,"product_code_c":"CONNECT","expiration_date":"","product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Connect"}},"product_edition_c":"SELL_UNKNOWN","deployment_flavor_c":"Ent","customer_product_name_c":"Sell PREMIER"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell PREMIER","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-PREMIER","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                ['SUGAR_SELL_BUNDLE'],
                'any_key',
                true,
                false,
                ['SUGAR_SELL_BUNDLE'],
            ],
            'user\'s license type is SELL PREMIER Bundle get all' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":10,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":1639958400,"product_code_nc":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Connector for LinkedIn Sales Navigator"},"product-template-id-for-sell-PREMIER":{"quantity":10,"product_name":"Sell PREMIER","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1639958400,"product_code_c":"DISCOVER","expiration_date":"","product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Discover"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1639958400,"product_code_c":"CONNECT","expiration_date":"","product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Connect"}},"product_edition_c":"SELL_PREMIER","deployment_flavor_c":"Ent","customer_product_name_c":"Sell PREMIER"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell PREMIER","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-PREMIER","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                ['SUGAR_SELL_PREMIER_BUNDLE'],
                'any_key',
                true,
                true,
                [
                    'CONNECT',
                    'DISCOVER',
                    'SUGAR_SELL_PREMIER_BUNDLE',
                ],
            ],
            'user\'s license type is SELL ESSENTIALS get all' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"product-template-id-for-sell-ESSENTIALS":{"quantity":10,"product_name":"Sell ESSENTIALS","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1639958400,"product_code_c":"DISCOVER","expiration_date":"","product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Discover"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1639958400,"product_code_c":"CONNECT","expiration_date":"","product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Connect"}},"product_edition_c":"SELL_ESSENTIALS","deployment_flavor_c":"Ent","customer_product_name_c":"Sell PREMIER"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell PREMIER","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-PREMIER","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                ['SUGAR_SELL_ESSENTIALS'],
                'any_key',
                true,
                true,
                [
                    'CONNECT',
                    'DISCOVER',
                    'SUGAR_SELL_ESSENTIALS',
                ],
                'user\'s license type is SELL ESSENTIALS, no bundled products, get all' => [
                    '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"product-template-id-for-sell-ESSENTIALS":{"quantity":10,"product_name":"Sell ESSENTIALS","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600,"product_edition_c":"SELL_ESSENTIALS","deployment_flavor_c":"Ent","customer_product_name_c":"Sell PREMIER"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell PREMIER","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-PREMIER","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                    ['SUGAR_SELL_ESSENTIALS'],
                    'any_key',
                    true,
                    true,
                    [
                        'SUGAR_SELL_ESSENTIALS',
                    ],
                ],
            ],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     *
     * @covers ::getSystemSubscriptionByKey
     * @covers ::getCustomerProductNameByKey
     * @covers ::getBundledSubscriptionsByKey
     *
     * @dataProvider getUserSubscriptionByKeyProvider
     */
    public function testGetUserSubscriptionByKey($data, $subscriptionKey, $expected)
    {
        $subMock = $this->getMockBuilder(SubscriptionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSubscription', 'getLicenseKey'])
            ->getMock();

        $subMock->expects($this->any())
            ->method('getSubscription')
            ->will($this->returnValue(new Subscription($data)));

        $subMock->expects($this->any())
            ->method('getLicenseKey')
            ->will($this->returnValue($subscriptionKey));

        $this->assertSame($expected, $subMock->getSystemSubscriptionByKey($subscriptionKey));
        $this->assertSame($expected['customer_product_name'] ?? '', $subMock->getCustomerProductNameByKey($subscriptionKey));
        $this->assertSame($expected['bundled_products'] ?? [], $subMock->getBundledSubscriptionsByKey($subscriptionKey));
    }

    public function getUserSubscriptionByKeyProvider()
    {
        // @codingStandardsIgnoreStart
        return [
            'product is SERVE + ENT, key is SERVE' => [
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":100,"product_name":"Sugar Enterprise","start_date_c":1556175600,"product_code_c":"ENT","expiration_date":4102473600,"deployment_flavor_c":"Ent"}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                'SUGAR_SERVE',
                [
                    'quantity' => 10,
                    'expiration_date' => 4102473600,
                    'start_date' => 1556175600,
                    'customer_product_name' => null,
                    'bundled_products' => [],
                ],
            ],
            'product is SERVE, key is SERVE' => [
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
            'non-exist key' => [
                '{"success":true,"error":"","subscription":{"id":"ffffc6a2-6ac3-11e9-b0f5-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597765,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"47fa5aa6620415261cd7bcd2a8de6d31","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                'any_key',
                [],
            ],
            'bundle subscription, key is a bundle' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":10,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":1639958400,"product_code_nc":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Connector for LinkedIn Sales Navigator"},"product-template-id-for-sell-PREMIER":{"quantity":10,"product_name":"Sell PREMIER","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1639958400,"product_code_c":"DISCOVER","expiration_date":"","product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Discover"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1639958400,"product_code_c":"CONNECT","expiration_date":"","product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Connect"}},"product_edition_c":"SELL_PREMIERE","deployment_flavor_c":"Ent","customer_product_name_c":"Sell PREMIER"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell PREMIER","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-PREMIER","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                'SUGAR_SELL_BUNDLE',
                [
                    'quantity' => 10,
                    'expiration_date' => 4102473600,
                    'start_date' => 1639958400,
                    'customer_product_name' => 'Sell PREMIER',
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
            'SELL Bundle, key is a bundled product key' => [
                '{"success":true,"error":"","subscription":{"id":"92b2c2da-5f9d-11ec-87db-06e41dba421a","addons":{"6c6acf06-d93b-11e7-9231-02c10f456dba":{"quantity":10,"product_name":"Sugar Connector for LinkedIn Sales Navigator","start_date_c":1639958400,"product_code_nc":null,"expiration_date":4102473600,"product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Connector for LinkedIn Sales Navigator"},"product-template-id-for-sell-PREMIER":{"quantity":10,"product_name":"Sell PREMIER","start_date_c":1639958400,"product_code_c":"SELL","expiration_date":4102473600,"bundled_products":{"4db82ab6-40bd-11ec-a593-06eddc549468":{"quantity":10,"product_name":"Sugar Discover","start_date_c":1639958400,"product_code_c":"DISCOVER","expiration_date":"","product_edition_c":"","deployment_flavor_c":null,"customer_product_name_c":"Sugar Discover"},"ab0fabd8-5cba-11e9-9dba-02c10f456dba":{"quantity":10,"product_name":"Sugar Connect","start_date_c":1639958400,"product_code_c":"CONNECT","expiration_date":"","product_edition_c":"","deployment_flavor_c":"","customer_product_name_c":"Sugar Connect"}},"product_edition_c":"SELL_PREMIERE","deployment_flavor_c":"Ent","customer_product_name_c":"Sell PREMIER"}},"emails":["cmoreno@sugarcrm.com"],"status":"enabled","audited":1,"domains":["sugarcrm.com"],"product":"Sugar Sell PREMIER","perpetual":0,"account_id":"3cb06858-31f2-5638-9751-4be429b99752","quantity_c":10,"account_name":"San Francisco Giants (stage test account2) x","account_type":"Customer","date_entered":1639789087,"evaluation_c":0,"portal_users":0,"date_modified":1640034397,"partner_type_c":"","expiration_date":4102473600,"subscription_id":"0643a69fcd177d64c70ec9e620656a88","term_end_date_c":4102473600,"term_start_date_c":1639958400,"account_partner_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","enforce_user_limit":0,"od_instance_name_c":"","account_partner_name":"iApps Test Partner Account","enforce_portal_users":0,"producttemplate_id_c":"product-template-id-for-sell-PREMIER","account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":""}}',
                'CONNECT',
                [
                    'quantity' => 10,
                    'expiration_date' => 4102473600,
                    'start_date' => 1639958400,
                    'customer_product_name' => 'Sugar Connect',
                    'bundled_products' => [],
                ],
            ],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * @covers ::getUserInvalidSubscriptions
     *
     * @dataProvider getUserValidSubscriptionsProvider
     */
    public function testGetUserValidSubscriptions($sysKeys, $userLicenseType, $expected)
    {
        $userMock = $this->getMockBuilder(\User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLicenseTypes'])
            ->getMock();

        $userMock->expects($this->any())
            ->method('getLicenseTypes')
            ->will($this->returnValue($userLicenseType));

        $userMock->id = '1111111';

        $subMock = $this->getMockBuilder(SubscriptionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSystemSubscriptionKeys'])
            ->getMock();

        $subMock->expects($this->any())
            ->method('getSystemSubscriptionKeys')
            ->will($this->returnValue($sysKeys));

        $result = TestReflection::callProtectedMethod($subMock, 'getUserInvalidSubscriptions', [$userMock]);
        $this->assertSame($expected, $result);
    }

    public function getUserValidSubscriptionsProvider()
    {
        return [
            [
                [
                    'SUGAR_SERVE' => true,
                    'SUGAR_SELL' => true,
                ],
                ['CURRENT'],
                ['CURRENT'],
            ],
            [
                [
                    'SUGAR_SERVE' => true,
                    'CURRENT' => true,
                ],
                ['CURRENT'],
                [],
            ],
            [
                [
                    'SUGAR_SERVE' => true,
                    'CURRENT' => true,
                ],
                ['SUGAR_SELL', 'CURRENT'],
                ['SUGAR_SELL'],
            ],
            [
                [],
                ['SUGAR_SELL'],
                ['SUGAR_SELL'],
            ],
            [
                [
                    'SUGAR_SERVE' => true,
                    'CURRENT' => true,
                ],
                [],
                [],
            ],
        ];
    }

    /**
     * @covers ::getTotalNumberOfMangoUsers
     *
     * @dataProvider getTotalNumberOfMangoUsersProvider
     */
    public function testGetTotalNumberOfUsers($data, $expected)
    {

        $subMock = $this->getMockBuilder(SubscriptionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSystemSubscriptions'])
            ->getMock();

        $subMock->expects($this->any())
            ->method('getSystemSubscriptions')
            ->will($this->returnValue($data));

        $this->assertSame($expected, $subMock->getTotalNumberOfMangoUsers());
    }

    public function getTotalNumberOfMangoUsersProvider()
    {
        return [
            'product is SERVE + ENT' => [
                [
                    'SUGAR_SERVE' => ['quantity' => 10, 'expiration_date' => 1587798000],
                    'CURRENT' => ['quantity' => 1000, 'expiration_date' => 1587798000],
                ],
                1010,
            ],
            'product is SERVE' => [
                [
                    'SUGAR_SERVE' => ['quantity' => 10, 'expiration_date' => 1587798000],
                ],
                10,
            ],
            'product is SERVE + HINT' => [
                [
                    'SUGAR_SERVE' => ['quantity' => 10, 'expiration_date' => 1587798000],
                    'SUGAR_HINT' => ['quantity' => 100, 'expiration_date' => 1587798000],
                ],
                10,
            ],
            'empty' => [
                [],
                0,
            ],
        ];
    }

    /**
     * @covers ::isSingleMangoTypeEntitlement
     *
     * @dataProvider isSingleMangoTypeEntitlementProvider
     */
    public function testIsSingleMangoTypeEntitlement($data, $expected)
    {

        $subMock = $this->getMockBuilder(SubscriptionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSystemSubscriptions'])
            ->getMock();

        $subMock->expects($this->any())
            ->method('getSystemSubscriptions')
            ->will($this->returnValue($data));

        $this->assertSame($expected, $subMock->isSingleMangoTypeEntitlement());
    }

    public function isSingleMangoTypeEntitlementProvider(): array
    {
        return [
            'product is SERVE + ENT' => [
                [
                    'SUGAR_SERVE' => ['quantity' => 10, 'expiration_date' => 1587798000],
                    'CURRENT' => ['quantity' => 1000, 'expiration_date' => 1587798000],
                ],
                false,
            ],
            'product is SERVE' => [
                [
                    'SUGAR_SERVE' => ['quantity' => 10, 'expiration_date' => 1587798000],
                ],
                true,
            ],
            'product is SERVE + HINT' => [
                [
                    'SUGAR_SERVE' => ['quantity' => 10, 'expiration_date' => 1587798000],
                    'SUGAR_HINT' => ['quantity' => 100, 'expiration_date' => 1587798000],
                ],
                true,
            ],
            'empty' => [
                [],
                true,
            ],
        ];
    }

    /**
     * @covers ::getSystemSubscriptionSeatsByType
     * @param $data
     * @param string $type
     * @param $expected
     *
     * @dataProvider getSystemSubscriptionSeatsByTypeProvider
     */
    public function testGetSystemSubscriptionSeatsByType(array $data, string $type, $expected)
    {
        $subMock = $this->getMockBuilder(SubscriptionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSystemSubscriptions'])
            ->getMock();

        $subMock->expects($this->any())
            ->method('getSystemSubscriptions')
            ->will($this->returnValue($data));

        $this->assertSame($expected, $subMock->getSystemSubscriptionSeatsByType($type));
    }

    public function getSystemSubscriptionSeatsByTypeProvider()
    {
        return [
            'get SERVE for SERVE + ENT' => [
                [
                    'SUGAR_SERVE' => ['quantity' => 10, 'expiration_date' => 1587798000],
                    'CURRENT' => ['quantity' => 1000, 'expiration_date' => 1587798000],
                ],
                'SUGAR_SERVE',
                10,
            ],
            'get ENT for SERVE + ENT' => [
                [
                    'SUGAR_SERVE' => ['quantity' => 10, 'expiration_date' => 1587798000],
                    'CURRENT' => ['quantity' => 1000, 'expiration_date' => 1587798000],
                ],
                'CURRENT',
                1000,
            ],
            'get ENT for SERVE only' => [
                [
                    'SUGAR_SERVE' => ['quantity' => 10, 'expiration_date' => 1587798000],
                ],
                'CURRENT',
                0,
            ],
            'get ENT for empty response' => [
                [],
                'CURRENT',
                0,
            ],
            'get ENT for no quantity' => [
                [
                    'CURRENT' => [],
                ],
                'CURRENT',
                0,
            ],
        ];
    }

    /**
     * @covers ::getSystemSubscriptionSeats
     * @param $data
     * @param string $type
     * @param $expected
     *
     * @dataProvider getSystemSubscriptionSeatsProvider
     */
    public function testGetSystemSubscriptionSeats(array $data, array $expected)
    {
        $subMock = $this->getMockBuilder(SubscriptionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSystemSubscriptions'])
            ->getMock();

        $subMock->expects($this->any())
            ->method('getSystemSubscriptions')
            ->will($this->returnValue($data));

        $this->assertSame($expected, $subMock->getSystemSubscriptionSeats());
    }

    public function getSystemSubscriptionSeatsProvider()
    {
        return [
            // product is SERVE + ENT
            [
                [
                    'SUGAR_SERVE' => ['quantity' => 10, 'expiration_date' => 1587798000],
                    'CURRENT' => ['quantity' => 1000, 'expiration_date' => 1587798000],
                ],
                ['SUGAR_SERVE' => 10, 'CURRENT' => 1000],
            ],
            // product is SERVE
            [
                [
                    'SUGAR_SERVE' => ['quantity' => 10, 'expiration_date' => 1587798000],
                ],
                ['SUGAR_SERVE' => 10],
            ],
            [
                [],
                [],
            ],
        ];
    }

    /**
     * @covers ::hasSubscription
     * @param $data
     * @param string $type
     * @param $expected
     *
     * @dataProvider hasSubscriptionProvider
     */
    public function testHasSubscription(array $data, string $key, bool $includeImplied, bool $expected)
    {
        $subMock = $this->getMockBuilder(SubscriptionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllSystemSubscriptionKeys'])
            ->getMock();

        $subMock->expects($this->any())
            ->method('getAllSystemSubscriptionKeys')
            ->will($this->returnValue($data));

        $this->assertSame($expected, $subMock->hasSubscription($key, $includeImplied));
    }

    public function hasSubscriptionProvider()
    {
        return [
            'product is SERVE + ENT, $key is SERVE' => [
                [
                    'SUGAR_SERVE' => true,
                    'CURRENT' => true,
                ],
                'SUGAR_SERVE',
                true,
                true,
            ],
            'product is SERVE + ENT, $key is wrong key' => [
                [
                    'SUGAR_SERVE' => true,
                    'CURRENT' => true,
                    'SUGAR_SELL_PREMIER_BUNDLE',
                    'CONNECT' => true,
                    'DISCOVER' => true,
                ],
                'SELLEE',
                true,
                false,
            ],
            'product is SERVE + CURRENT + SUGAR_SELL_PREMIER_BUNDLE, $key is SELL, include implied keys' => [
                [
                    'SUGAR_SERVE' => true,
                    'CURRENT' => true,
                    'SUGAR_SELL_PREMIER_BUNDLE' => true,
                    'CONNECT' => true,
                    'DISCOVER' => true,
                ],
                'SUGAR_SELL',
                true,
                true,
            ],
            'product is SERVE + CURRENT + SUGAR_SELL_ESSENTIALS, $key is SELL, include implied keys' => [
                [
                    'SUGAR_SERVE' => true,
                    'CURRENT' => true,
                    'SUGAR_SELL_ESSENTIALS' => true,
                ],
                'SUGAR_SELL',
                true,
                true,
            ],
            'product is SERVE + CURRENT + SUGAR_SELL_BUNDLES, $key is SELL, include implied keys' => [
                [
                    'SUGAR_SERVE' => true,
                    'CURRENT' => true,
                    'SUGAR_SELL_BUNDLE' => true,
                ],
                'SUGAR_SELL',
                true,
                true,
            ],
            'product is SERVE + SUGAR_SELL_PREMIER_BUNDLE, $key is SELL, do not include implied keys' => [
                [
                    'SUGAR_SERVE' => true,
                    'CURRENT' => true,
                    'SUGAR_SELL_PREMIER_BUNDLE' => true,
                    'CONNECT' => true,
                    'DISCOVER' => true,
                ],
                'SUGAR_SELL',
                false,
                false,
            ],
            'product is SERVE + SUGAR_SELL_PREMIER_BUNDLE, $key is CONNECT, include implied keys' => [
                [
                    'SUGAR_SERVE' => true,
                    'CURRENT' => true,
                    'SUGAR_SELL_PREMIER_BUNDLE' => true,
                    'CONNECT' => true,
                    'DISCOVER' => true,
                ],
                'CONNECT',
                true,
                true,
            ],
            'product is SERVE + SUGAR_SELL_PREMIER_BUNDLE, $key is CONNECT, do not include implied keys' => [
                [
                    'SUGAR_SERVE' => true,
                    'CURRENT' => true,
                    'SUGAR_SELL_PREMIER_BUNDLE' => true,
                    'CONNECT' => true,
                    'DISCOVER' => true,
                ],
                'CONNECT',
                false,
                true,
            ],
        ];
    }

    /**
     * @covers ::getAllSubsetsOfSystemSubscriptions
     * @covers ::sortSubscriptionKeys
     *
     * @param array $keys
     * @param $expected
     *
     * @dataProvider getAllSubsetsOfSystemSubscriptionsProvider
     */
    public function testGetAllSubsetsOfSystemSubscriptions(array $keys, array $expected)
    {
        $subMock = $this->getMockBuilder(SubscriptionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSystemSubscriptionKeys'])
            ->getMock();

        $subMock->expects($this->any())
            ->method('getSystemSubscriptionKeys')
            ->will($this->returnValue($keys));

        $this->assertSame($expected, $subMock->getAllSubsetsOfSystemSubscriptions());
    }

    public function getAllSubsetsOfSystemSubscriptionsProvider()
    {
        return [
            'single subscription' => [
                ['SUGAR_SELL' => true],
                [
                    ['SUGAR_SELL'],
                ],
            ],
            'mutiple subscriptions' => [
                [
                    'SUGAR_SELL' => true,
                    'CURRENT' => true,
                    'SUGAR_SERVE' => true,
                ],
                [
                    ['CURRENT'],
                    ['SUGAR_SELL'],
                    ['CURRENT', 'SUGAR_SELL'],
                    ['SUGAR_SERVE'],
                    ['CURRENT', 'SUGAR_SERVE'],
                    ['SUGAR_SELL', 'SUGAR_SERVE'],
                    ['CURRENT', 'SUGAR_SELL', 'SUGAR_SERVE'],
                ],
            ],
            'empty subscriptions' => [
                [],
                [],
            ],
        ];
    }

    /**
     * @covers ::getUserLicenseTypesInString
     * @param array|null $data
     * @param $expected
     *
     * @dataProvider getUserLicenseTypesInStringProvider
     */
    public function testGetUserLicenseTypesInString(?array $data, $expected)
    {
        $subMock = $this->getMockBuilder(SubscriptionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserSubscriptions'])
            ->getMock();

        $subMock->expects($this->any())
            ->method('getUserSubscriptions')
            ->will($this->returnValue($data));

        $userMock = $this->getMockBuilder(\User::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();

        $this->assertSame($expected, $subMock->getUserLicenseTypesInString($userMock));
    }

    public function getUserLicenseTypesInStringProvider()
    {
        return [
            'single subscription' => [
                ['SUGAR_SELL'],
                'SUGAR_SELL',
            ],
            'multiple subscriptions' => [
                [
                    'SUGAR_SELL',
                    'CURRENT',
                    'SUGAR_SERVE',
                ],
                'SUGAR_SELL_CURRENT_SUGAR_SERVE',
            ],
            'empty subscriptions' => [
                [],
                '',
            ],
        ];
    }

    /**
     * @covers ::getSystemSubscriptionKeysInSortedValueArray
     * @param array $userSubscriptions
     * @param array $sysSubscriptions
     * @param bool $expected
     *
     * @dataProvider getSystemSubscriptionKeysInSortedValueArrayProvider
     */
    public function testGetSystemSubscriptionKeysInSortedValueArray($sysSubscriptions, $expected)
    {
        $subMock = $this->getMockBuilder(SubscriptionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSystemSubscriptionKeys'])
            ->getMock();

        $subMock->expects($this->any())
            ->method('getSystemSubscriptionKeys')
            ->will($this->returnValue($sysSubscriptions));

        $this->assertSame($expected, $subMock->getSystemSubscriptionKeysInSortedValueArray());
    }

    public function getSystemSubscriptionKeysInSortedValueArrayProvider()
    {
        return [
            'same subscription' => [
                ['CURRENT' => true],
                ['CURRENT'],
            ],
            'empty subscription' => [
                [],
                [],
            ],
            'sortingsubscriptions' => [
                ['SUGAR_SERVE' => true, 'CURRENT' => true],
                ['CURRENT', 'SUGAR_SERVE'],
            ],
        ];
    }


    /**
     * @covers ::getSystemLicenseTypesExceededLimit
     *
     * @dataProvider getSystemLicenseTypesExceededLimitProvider
     */
    public function testGetSystemLicenseTypesExceededLimit(
        array $subscription,
        array $activeUsersByLicenseTypes,
        array $expected,
        int   $expectedCount
    ) {

        $subMock = $this->getMockBuilder(SubscriptionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSystemSubscriptions', 'getSystemUserCountByLicenseTypes'])
            ->getMock();

        $subMock->expects($this->any())
            ->method('getSystemSubscriptions')
            ->will($this->returnValue($subscription));

        $subMock->expects($this->any())
            ->method('getSystemUserCountByLicenseTypes')
            ->will($this->returnValue($activeUsersByLicenseTypes));

        $license_seats_needed = 0;
        $this->assertSame($expected, $subMock->getSystemLicenseTypesExceededLimit($license_seats_needed), 'no match for exceededLimit array');
        $this->assertEquals($expectedCount, $license_seats_needed, 'no match for exceeded limit counts');
    }

    public function getSystemLicenseTypesExceededLimitProvider()
    {
        return [
            'SERVE + ENT instance, user with SERVE only license Type no exceeded limit' => [
                [
                    'SUGAR_SERVE' => ['quantity' => 10, 'expiration_date' => 1587798000],
                    'CURRENT' => ['quantity' => 1000, 'expiration_date' => 1587798000],
                ],
                ['SUGAR_SERVE' => 10],
                [],
                0,
            ],
            'SERVE + ENT instance, user with CURRENT license Type over limit' => [
                [
                    'SUGAR_SERVE' => ['quantity' => 10, 'expiration_date' => 1587798000],
                    'CURRENT' => ['quantity' => 1000, 'expiration_date' => 1587798000],
                ],
                ['SUGAR_SERVE' => 10, 'CURRENT' => 10000],
                ['CURRENT' => 9000],
                9000,
            ],
            'SERVE instance, user with SERVE only license Type exceeded limits' => [
                [
                    'SUGAR_SERVE' => ['quantity' => 10, 'expiration_date' => 1587798000],
                ],
                ['SUGAR_SERVE' => 100],
                ['SUGAR_SERVE' => 90],
                90,
            ],
            'SERVE instance, user with SERVE and CURRENT license Types exceeded limits' => [
                [
                    'SUGAR_SERVE' => ['quantity' => 10, 'expiration_date' => 1587798000],
                ],
                ['SUGAR_SERVE' => 100, 'CURRENT' => 10000],
                ['SUGAR_SERVE' => 90, 'CURRENT' => 10000],
                10090,
            ],
            'The instance without subscription' => [
                [],
                ['SUGAR_SERVE' => 10, 'CURRENT' => 10000],
                ['CURRENT' => 1],
                1,
            ],
        ];
    }

    /**
     * @covers ::getUserExceededAndInvalidLicenseTypes
     *
     * @dataProvider getUserExceededAndInvalidLicenseTypesProvider
     */
    public function testGetUserExceededAndInvalidLicenseTypes(
        array $licenseTypesExceededLimit,
        array $userSubscription,
        array $invalidSubscription,
        array $expected
    ) {

        $subMock = $this->getMockBuilder(SubscriptionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserSubscriptions', 'getUserInvalidSubscriptions', 'getSystemLicenseTypesExceededLimit'])
            ->getMock();

        $subMock->expects($this->any())
            ->method('getSystemLicenseTypesExceededLimit')
            ->will($this->returnValue($licenseTypesExceededLimit));

        $subMock->expects($this->any())
            ->method('getUserSubscriptions')
            ->will($this->returnValue($userSubscription));

        $subMock->expects($this->any())
            ->method('getUserInvalidSubscriptions')
            ->will($this->returnValue($invalidSubscription));

        $userMock = $this->getMockBuilder(\User::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();

        $this->assertSame($expected, $subMock->getUserExceededAndInvalidLicenseTypes($userMock));
    }

    public function getUserExceededAndInvalidLicenseTypesProvider()
    {
        return [
            'user has license type exceed the limit' => [
                ['SUGAR_SERVE' => 10],
                ['SUGAR_SERVE'],
                [],
                ['SUGAR_SERVE'],
            ],
            'user has license type exceed the limit and has invalid license type' => [
                ['SUGAR_SERVE' => 10, 'UNKNOWN_TYPE' => 1000],
                ['SUGAR_SERVE'],
                ['INVALID_TYPE'],
                ['SUGAR_SERVE', 'INVALID_TYPE'],
            ],
            'user has invalid license type' => [
                [],
                ['SUGAR_SERVE'],
                ['INVALID_TYPE'],
                ['INVALID_TYPE'],
            ],
        ];
    }

    /**
     * @return array
     */
    public function providerTestGetUserExceededLicenseTypes()
    {
        return [
            'empty system license types' => [
                [],
                [Subscription::SUGAR_SERVE_KEY],
                'anyname',
                [Subscription::SUGAR_SERVE_KEY],
            ],
            'used up all allowed seats for non-support user' => [
                [
                    Subscription::SUGAR_SERVE_KEY => ['quantity' => 10],
                    Subscription::SUGAR_BASIC_KEY => ['quantity' => 100],
                ],
                [Subscription::SUGAR_SERVE_KEY, Subscription::SUGAR_SELL_KEY, Subscription::SUGAR_BASIC_KEY],
                'anyname',
                [1 => Subscription::SUGAR_SELL_KEY],
            ],
            'all other cases with non-support user' => [
                [
                    Subscription::SUGAR_SERVE_KEY => ['quantity' => 9],
                    Subscription::SUGAR_BASIC_KEY => ['quantity' => 100],
                ],
                [Subscription::SUGAR_SERVE_KEY, Subscription::SUGAR_SELL_KEY, Subscription::SUGAR_BASIC_KEY],
                'anyname',
                [Subscription::SUGAR_SERVE_KEY, Subscription::SUGAR_SELL_KEY],
            ],
            'support user' => [
                [
                    Subscription::SUGAR_SERVE_KEY => ['quantity' => 10],
                    Subscription::SUGAR_BASIC_KEY => ['quantity' => 100],
                ],
                [Subscription::SUGAR_SERVE_KEY, Subscription::SUGAR_SELL_KEY, Subscription::SUGAR_BASIC_KEY],
                \User::SUPPORT_USER_NAME,
                [],
            ],
            'support provision user' => [
                [
                    Subscription::SUGAR_SERVE_KEY => ['quantity' => 10],
                    Subscription::SUGAR_BASIC_KEY => ['quantity' => 100],
                ],
                [Subscription::SUGAR_SERVE_KEY, Subscription::SUGAR_SELL_KEY, Subscription::SUGAR_BASIC_KEY],
                \User::SUPPORT_PROVISION_USER_NAME,
                [],
            ],
            'support upgrade user' => [
                [
                    Subscription::SUGAR_SERVE_KEY => ['quantity' => 10],
                    Subscription::SUGAR_BASIC_KEY => ['quantity' => 100],
                ],
                [Subscription::SUGAR_SERVE_KEY, Subscription::SUGAR_SELL_KEY, Subscription::SUGAR_BASIC_KEY],
                \User::SUPPORT_UPGRADE_USER_NAME,
                [],
            ],
            'support portal user' => [
                [
                    Subscription::SUGAR_SERVE_KEY => ['quantity' => 10],
                    Subscription::SUGAR_BASIC_KEY => ['quantity' => 100],
                ],
                [Subscription::SUGAR_SERVE_KEY, Subscription::SUGAR_SELL_KEY, Subscription::SUGAR_BASIC_KEY],
                \User::SUPPORT_PORTAL_USER,
                [],
            ],
        ];
    }

    /**
     * @covers ::getUserExceededLicenseTypes
     * @dataProvider providerTestGetUserExceededLicenseTypes
     * @param $allowedSeats
     * @param $userTypes
     * @param $result
     */
    public function testGetUserExceededLicenseTypes($allowedSeats, $userTypes, $userName, $expected)
    {
        /** @var \User|MockObject $user */
        $user = $this->getMockBuilder(\User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $user->user_name = $userName;

        /** @var SubscriptionManager|MockObject $manager */
        $manager = $this->getMockBuilder(SubscriptionManager::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getSystemUserCountByLicenseTypes',
                'getSystemSubscriptions',
                'getUserSubscriptions',
            ])->getMock();

        $manager->expects($this->any())
            ->method('getSystemUserCountByLicenseTypes')
            ->willReturn([
                Subscription::SUGAR_SERVE_KEY => 10,
                Subscription::SUGAR_BASIC_KEY => 1,
            ]);

        $manager->expects($this->any())
            ->method('getSystemSubscriptions')
            ->willReturn($allowedSeats);

        $manager->expects($this->any())
            ->method('getUserSubscriptions')
            ->willReturn($userTypes);

        $this->assertEquals($expected, $manager->getUserExceededLicenseTypes($user));
    }

    /**
     * @covers ::isSubscriptionChanged
     * @param array $currentSubscriptionKeys
     * @param string $oldSubData
     * @param $expected
     *
     * @dataProvider providorIsSubscriptionChanged
     */
    public function testIsSubscriptionChanged(array $currentSubscriptionKeys, string $oldSubData, $expected)
    {
        $subMock = $this->getMockBuilder(SubscriptionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSystemSubscriptionKeys'])
            ->getMock();

        $subMock->expects($this->any())
            ->method('getSystemSubscriptionKeys')
            ->will($this->returnValue($currentSubscriptionKeys));

        $changed = TestReflection::callProtectedMethod($subMock, 'isSubscriptionChanged', [$oldSubData]);
        $this->assertSame($expected, $changed);
    }

    public function providorIsSubscriptionChanged()
    {
        // @codingStandardsIgnoreStart
        return [
            'no change, SERVE, old product is SERVE' => [
                ['SUGAR_SERVE' => true],
                '{"success":true,"error":"","subscription":{"id":"ffffc6a2-6ac3-11e9-b0f5-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597765,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"47fa5aa6620415261cd7bcd2a8de6d31","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                false,
            ],
            'changed, SERVE, old product is SERVE + ENT' => [
                ['SUGAR_SERVE' => true],
                '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":4102473600,"deployment_flavor_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":100,"product_name":"Sugar Enterprise","start_date_c":1556175600,"product_code_c":"ENT","expiration_date":4102473600,"deployment_flavor_c":"Ent"}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":4102473600,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":4102473600,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}',
                true,
            ],
            'changed, old prodution has no subscription' => [
                ['CURRENT' => true],
                '{"no subscription section": {"quantity" : "100"}}',
                true,
            ],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * @covers ::getAllImpliedSubscriptions
     * @param array $keys
     * @param array $expected
     *
     * @dataProvider providerGetAllImpliedSubscriptions
     */
    public function testGetAllImpliedSubscriptions(array $keys, array $expected)
    {
        $subMock = $this->getMockBuilder(SubscriptionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['isBundleKey', 'getBundledKeys'])
            ->getMock();

        $this->assertSame($expected, $subMock->getAllImpliedSubscriptions($keys));
    }

    public function providerGetAllImpliedSubscriptions()
    {
        return [
            'no implied subscriptions' => [
                [Subscription::SUGAR_SELL_KEY, Subscription::SUGAR_HINT_KEY],
                [Subscription::SUGAR_SELL_KEY, Subscription::SUGAR_HINT_KEY],
            ],
            'bundle with implied subscriptions' => [
                [
                    Subscription::SUGAR_SELL_ADVANCED_BUNDLE_KEY,
                    Subscription::SUGAR_HINT_KEY,
                ],
                [
                    Subscription::SUGAR_SELL_KEY,
                    Subscription::SUGAR_SELL_ADVANCED_BUNDLE_KEY,
                    Subscription::SUGAR_HINT_KEY,
                ],
            ],
        ];
    }
}
