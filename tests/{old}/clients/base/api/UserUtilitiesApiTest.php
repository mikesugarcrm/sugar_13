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

class UserUtilitiesApiTest extends TestCase
{
    /**
     * @var UserUtilitiesApi
     */
    private $api;

    /**
     * @var RestService
     */
    protected $serviceMock;

    /**
     * setUpBeforeClass function
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        global $current_user;
        /**
         * @var User
         */
        $current_user = BeanFactory::newBean('Users');
        $current_user->getSystemUser();
    }

    /**
     * tearDownAfterClass function
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        global $current_user;
        $current_user = null;
    }

    /**
     * setUp function
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->api = $this->getMockBuilder('UserUtilitiesApi')
            ->onlyMethods([])
            ->getMock();

        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    /**
     * tearDown function
     *
     * @return void
     */
    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Data provider for the locale test
     *
     * @return array
     */
    public function providerGetLocaleData(): array
    {
        return [
            [
                'args' => [
                    'userId' => '1',
                ],
            ],
        ];
    }

    /**
     * Retrieve a user's locale data
     *
     * @param array $args
     *
     * @dataProvider providerGetLocaleData
     */
    public function testGetLocaleData(array $args): void
    {
        $result = $this->api->getLocaleData($this->serviceMock, $args);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('datef', $result);
        $this->assertArrayHasKey('timef', $result);
        $this->assertArrayHasKey('currency', $result);
        $this->assertArrayHasKey('defaultCurrencySignificantDigits', $result);
        $this->assertArrayHasKey('timezone', $result);
        $this->assertArrayHasKey('ut', $result);
        $this->assertArrayHasKey('numGrpSep', $result);
        $this->assertArrayHasKey('defaultLocaleNameFormat', $result);
        $this->assertArrayHasKey('decSep', $result);
        $this->assertArrayHasKey('timeOptions', $result);
        $this->assertArrayHasKey('dateOptions', $result);
        $this->assertArrayHasKey('nameOptions', $result);
        $this->assertArrayHasKey('currencyOptions', $result);
        $this->assertArrayHasKey('sigDigitsOptions', $result);
        $this->assertArrayHasKey('timezoneOptions', $result);
        $this->assertArrayHasKey('wizardPrompt', $result);
        $this->assertArrayHasKey('appearance', $result);
    }


    /**
     * Data provider for the actions
     *
     * @return array
     */
    public function providerPerformActions(): array
    {
        return [
            [
                'args' => [
                    'actions' => [
                        [
                            'type' => 'CopyDashboards',
                            'dashboards' => ['did1', 'did2'],
                            'modules' => [],
                            'sourceUser' => '1',
                            'destinationUsers' => [],
                        ],
                        [
                            'type' => 'CopyFilters',
                            'filters' => ['fid1', 'fid2'],
                            'modules' => [],
                            'sourceUser' => '1',
                            'destinationUsers' => [],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Perform user utils actions
     *
     * @param array $args
     *
     * @dataProvider providerPerformActions
     */
    public function testPerformActions(array $args): void
    {
        $result = $this->api->performActions($this->serviceMock, $args);
        $this->assertTrue($result);
    }
}
