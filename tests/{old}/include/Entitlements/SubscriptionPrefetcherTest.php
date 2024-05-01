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
use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use Sugarcrm\Sugarcrm\Entitlements\SubscriptionPrefetcher;
use Sugarcrm\Sugarcrm\Logger\Factory;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Entitlements\SubscriptionPrefetcher
 */
class SubscriptionPrefetcherTest extends TestCase
{
    public const VALID_LICENSE_CONTENT = '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":10,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":1898582400,"deployment_flavor_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":100,"product_name":"Sugar Enterprise","start_date_c":1556175600,"product_code_c":"ENT","expiration_date":1898582400,"deployment_flavor_c":"Ent"}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1898582400,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1898582400,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}';

    public const VALID_CHANGED_LICENSE_CONTENT = '{"success":true,"error":"","subscription":{"id":"9c9f882c-6ac3-11e9-a884-02c10f456dba","debug":0,"addons":{"aa8834fa-6ac0-11e9-b588-02c10f456aaa":{"quantity":11,"product_name":"Sugar Serve (DEV ONLY)","start_date_c":1556175600,"product_code_c":"SERVE","expiration_date":1898582400,"deployment_flavor_c":""},"b8d64dc8-4235-f4ad-a2b9-4c4ee85b80ae":{"quantity":100,"product_name":"Sugar Enterprise","start_date_c":1556175600,"product_code_c":"ENT","expiration_date":1898582400,"deployment_flavor_c":"Ent"}},"emails":[],"status":"enabled","audited":1,"domains":[],"perpetual":0,"account_id":"1f978c6b-df8e-33f8-90ba-557f67e9a05e","account_name":"iApps Test Partner Account","account_type":"Partner","date_entered":1556597598,"evaluation_c":0,"portal_users":0,"date_modified":1558663202,"partner_type_c":"basic","perpetual_dd_c":"","expiration_date":1898582400,"subscription_id":"ad794561d946951952ce55d24a4617cf","term_end_date_c":1898582400,"term_start_date_c":1556175600,"account_partner_id":"","enforce_user_limit":1,"od_instance_name_c":"","account_partner_name":"","enforce_portal_users":0,"account_managing_team":"Channel","ignore_expiration_date":0,"od_instance_location_c":"us"}}';

    public const INVALID_LICENSE_CONTENT = 'invalid response';

    public function testSkipsIfIntervalNotPassed()
    {
        $mock = $this->createPrefetcherMock();

        $lastCheck = (new \DateTime())->format('Y-m-d H:i:s');
        $mock->method('getSetting')
            ->willReturnMap([
                [SubscriptionPrefetcher::KEY_LAST_CHECK, $lastCheck],
                [SubscriptionPrefetcher::KEY_LICENSE, 'fake_key'],
            ]);
        $mock->expects($this->never())->method('saveSetting');
        $mock->expects($this->never())->method('fetchLicenseContent');

        $this->assertFalse($mock->run());
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|SubscriptionPrefetcher
     */
    private function createPrefetcherMock()
    {
        $admin = $this->createMock(\Administration::class);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        return $this->getMockBuilder(SubscriptionPrefetcher::class)
            ->setConstructorArgs([$admin, $logger])
            ->onlyMethods(['getSetting', 'saveSetting', 'fetchLicenseContent'])
            ->getMock();
    }

    public function testDoesNotSaveIfSameContent()
    {
        $mock = $this->createPrefetcherMock();
        $lastCheck = (new \DateTime())
            ->sub(new \DateInterval(SubscriptionPrefetcher::PREFETCH_INTERVAL))
            ->format('Y-m-d H:i:s');
        $mock->method('getSetting')
            ->willReturnMap([
                [SubscriptionPrefetcher::KEY_LAST_CHECK, $lastCheck],
                [SubscriptionPrefetcher::KEY_LICENSE, 'fake_key'],
                [SubscriptionPrefetcher::KEY_STORED_CONTENT, self::VALID_LICENSE_CONTENT],
            ]);
        $mock->method('fetchLicenseContent')
            ->willReturn(self::VALID_LICENSE_CONTENT);
        $mock->expects($this->once())
            ->method('saveSetting')
            ->with(
                $this->equalTo(SubscriptionPrefetcher::KEY_LAST_CHECK),
                $this->callback(function ($date) {
                    return $this->timeApproximatelyEquals($date);
                })
            );

        $this->assertFalse($mock->run());
    }

    public function testDoesNotSaveIfInvalidLicense()
    {
        $mock = $this->createPrefetcherMock();
        $lastCheck = (new \DateTime())
            ->sub(new \DateInterval(SubscriptionPrefetcher::PREFETCH_INTERVAL))
            ->format('Y-m-d H:i:s');
        $mock->method('getSetting')
            ->willReturnMap([
                [SubscriptionPrefetcher::KEY_LAST_CHECK, $lastCheck],
                [SubscriptionPrefetcher::KEY_LICENSE, 'fake_key'],
            ]);
        $mock->expects($this->once())
            ->method('fetchLicenseContent')
            ->willReturn(self::INVALID_LICENSE_CONTENT);
        $mock->expects($this->once())
            ->method('saveSetting')
            ->with(
                $this->equalTo(SubscriptionPrefetcher::KEY_LAST_CHECK),
                $this->callback(function ($date) {
                    return $this->timeApproximatelyEquals($date);
                })
            );

        $this->assertFalse($mock->run());
    }

    public function testSuccessfullySave()
    {
        $mock = $this->createPrefetcherMock();
        $lastCheck = (new \DateTime())
            ->sub(new \DateInterval(SubscriptionPrefetcher::PREFETCH_INTERVAL))
            ->format('Y-m-d H:i:s');
        $mock->method('getSetting')
            ->willReturnMap([
                [SubscriptionPrefetcher::KEY_LAST_CHECK, $lastCheck],
                [SubscriptionPrefetcher::KEY_LICENSE, 'fake_key'],
                [SubscriptionPrefetcher::KEY_STORED_CONTENT, self::VALID_LICENSE_CONTENT],
            ]);
        $mock->method('fetchLicenseContent')
            ->willReturn(self::VALID_CHANGED_LICENSE_CONTENT);

        $result = [];
        $mock->method('saveSetting')
            ->willReturnCallback(function ($key, $value) use (&$result) {
                $result[$key] = $value;
            });

        $this->assertTrue($mock->run());
        $this->assertEquals(self::VALID_CHANGED_LICENSE_CONTENT, $result[SubscriptionPrefetcher::KEY_DOWNLOADED_CONTENT] ?? '');
        $this->assertTrue($this->timeApproximatelyEquals($result[SubscriptionPrefetcher::KEY_LAST_CHECK]), 'actual time does not match expected');
    }

    private function timeApproximatelyEquals(string $actual, ?\DateTime $expected = null)
    {
        $actual = DateTime::createFromFormat('Y-m-d H:i:s', $actual);
        if ($actual === false) {
            return false;
        }
        if ($expected === null) {
            $expected = new \DateTime();
        }
        return 2 > abs($actual->getTimestamp() - $expected->getTimestamp());
    }
}
