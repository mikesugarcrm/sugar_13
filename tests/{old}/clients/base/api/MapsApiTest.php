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
 * @group ApiTests
 */
class MapsApiTest extends TestCase
{
    /**
     * @var \RestService|mixed
     */
    public $serviceMock;
    public static $accounts;
    public static $geocodes;

    /** @var MapsApi */
    private $mapsApi;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');

        $startPointAccount = BeanFactory::newBean('Accounts');
        $startPointAccount->id = 'TEST-START-POINT-ACCOUNT-TESTX';
        $startPointAccount->new_with_id = true;
        $startPointAccount->name = 'START ACCOUNT Account';
        $startPointAccount->billing_address_postalcode = '01020';
        $startPointAccount->save();

        self::$accounts[] = $startPointAccount;

        for ($i = 0; $i < 6; $i++) {
            $account = BeanFactory::newBean('Accounts');
            $account->id = 'TEST-' . create_guid_section(10);
            $account->new_with_id = true;
            $account->name = "TEST $i Account";
            $account->billing_address_postalcode = ($i % 10) . '0210';
            $account->save();
            self::$accounts[] = $account;
        }

        $latitude = 44.3124237061;
        $longitude = 23.7884197235;

        for ($i = 0; $i < safeCount(self::$accounts); $i++) {
            $currentAccount = self::$accounts[$i];

            $geocode = BeanFactory::newBean('Geocode');
            $geocode->id = 'TEST-' . create_guid_section(10);
            $geocode->new_with_id = true;
            $geocode->country = 'Romania';
            $geocode->postalcode = '200302';
            $geocode->status = 'COMPLETED';
            $geocode->geocoded = true;
            $geocode->parent_name = $currentAccount->name;
            $geocode->parent_type = 'Accounts';
            $geocode->parent_id = $currentAccount->id;
            $geocode->latitude = $latitude;
            $geocode->longitude = $longitude;

            $geocode->save();

            self::$geocodes[] = $geocode;

            $latitude -= 0.1e-8;
        }

        $this->mapsApi = new MapsApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown(): void
    {
        if (safeCount(self::$accounts)) {
            $accountIds = [];

            foreach (self::$accounts as $account) {
                $accountIds[] = $account->id;
            }
            $accountIds = "('" . implode("','", $accountIds) . "')";

            $GLOBALS['db']->query("DELETE FROM accounts WHERE id IN {$accountIds}");

            if ($GLOBALS['db']->tableExists('accounts_cstm')) {
                $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id_c IN {$accountIds}");
            }
        }

        if (safeCount(self::$geocodes)) {
            $geocodeIds = [];

            foreach (self::$geocodes as $geocode) {
                $geocodeIds[] = $geocode->id;
            }
            $geocodeIds = "('" . implode("','", $geocodeIds) . "')";

            $GLOBALS['db']->query("DELETE FROM geocode WHERE id IN {$geocodeIds}");
        }

        SugarTestFilterUtilities::removeAllCreatedFilters();
        SugarTestHelper::tearDown();

        SugarConfig::getInstance()->clearCache();
    }

    /**
     * @param array $filterApiMeta
     *
     * @dataProvider providerMapsFilter
     */
    public function testMapsFilter($filterApiMeta)
    {
        $mapsConfigs = [
            'maps_modulesData' => [
                'Accounts' => [
                    'mappingType' => 'moduleFields',
                ],
            ],
        ];

        $admin = BeanFactory::getBean('Administration');
        $category = 'maps';
        $prefix = $category . '_';

        foreach ($mapsConfigs as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                $admin->saveSetting($category, str_replace($prefix, '', $key), $value, 'base');
            }
        }

        $reply = $this->mapsApi->filterList(
            $this->serviceMock,
            $filterApiMeta
        );

        $this->assertEquals(5, $reply['next_offset'], 'Simple: Next offset is not set correctly');
        $this->assertEquals(5, safeCount($reply['records']), 'Maps Api Test: Returned incorrect no of results');
    }

    /**
     * providerMapsFilter function
     *
     * demo data for testMapsFilter
     *
     * @return array
     */
    public function providerMapsFilter()
    {
        return [
            [
                'apiMetadata' => [
                    'module' => 'Accounts',
                    'filter' => [
                        [
                            '$distance' => [
                                '$in_radius_from_record' => [
                                    'unitType' => 'Miles',
                                    'radius' => '10',
                                    'recordId' => 'TEST-START-POINT-ACCOUNT-TESTX',
                                    'recordModule' => 'Accounts',
                                    'requiredFields' => [
                                        'name',
                                        'maps_distance',
                                        'maps_addressLine',
                                        'maps_locality',
                                        'my_favorite',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'max_num' => 5,
                    'order_by' => 'maps_distance:asc',
                    'fields' => 'name,maps_distance,maps_addressLine,maps_locality,my_favorite',
                ],
            ],
        ];
    }

    /**
     * Test API with empty filter
     */
    public function testEmptyFilterDefinition()
    {
        $result = $this->mapsApi->filterListSetup(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'filter' => '',
            ]
        );
        $this->assertEquals([], $result[0]['filter'], 'Empty definition: filter becomes empty array');
    }

    /**
     * Test API with empty longitude
     */
    public function testEmptyLongitude()
    {
        if (hasMapsLicense()) {
            $this->expectException(SugarApiExceptionMissingParameter::class);

            $this->mapsApi->getNearBy(
                $this->serviceMock,
                [
                    'radius' => '200',
                    'latitude' => '44.2758957190',
                ]
            );
        } else {
            $mapsLicenseLabel = translate('LBL_MAPS_NO_LICENSE_ACCESS');
            $this->assertEquals($mapsLicenseLabel, 'Maps License Required');
        }
    }
}
