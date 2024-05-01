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

/***
 * Used to test Forecast Module endpoints
 * @covers ForecastsApi
 */
class ForecastsRestApiTest extends TestCase
{
    /**
     * @var \User|mixed
     */
    //@codingStandardsIgnoreStart
    public $_user;
    //@codingStandardsIgnoreEnd

    private static $currentUser;
    private static $employee1;
    private static $employee2;
    private static $employee3;
    private static $employee4;

    public static function setUpBeforeClass(): void
    {
        SugarTestForecastUtilities::setUpForecastConfig();

        self::$currentUser = SugarTestUserUtilities::createAnonymousUser();
        self::$currentUser->user_name = 'employee0';
        self::$currentUser->save();

        self::$employee1 = SugarTestUserUtilities::createAnonymousUser();
        self::$employee1->reports_to_id = self::$currentUser->id;
        self::$employee1->user_name = 'employee1';
        self::$employee1->save();

        self::$employee2 = SugarTestUserUtilities::createAnonymousUser();
        self::$employee2->reports_to_id = self::$currentUser->id;
        self::$employee2->user_name = 'employee2';
        self::$employee2->save();

        self::$employee3 = SugarTestUserUtilities::createAnonymousUser();
        self::$employee3->reports_to_id = self::$employee2->id;
        self::$employee3->user_name = 'employee3';
        self::$employee3->save();

        self::$employee4 = SugarTestUserUtilities::createAnonymousUser();
        self::$employee4->reports_to_id = self::$employee3->id;
        self::$employee4->user_name = 'employee4';
        self::$employee4->save();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    protected function setUp(): void
    {
        //Create an anonymous user for login purposes/
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
        // call a commit for transactional dbs
        $GLOBALS['db']->commit();
    }

    /**
     * This test is to see that the data returned for the name field is set correctly when locale name format changes
     *
     * @group testGetLocaleFormattedName
     * @group forecastapi
     * @group forecasts
     * @covers ForecastsApi::getReportees
     */
    public function testGetLocaleFormattedNameReporteesEndpoint()
    {
        global $locale;
        $defaultPreference = $this->_user->getPreference('default_locale_name_format');
        $this->_user->setPreference('default_locale_name_format', 'l, f', 0, 'global');
        $this->_user->savePreferencesToDB();
        $this->_user->reloadPreferences();

        $api = new ForecastsApi();
        $restReply = $api->getReportees(SugarTestRestUtilities::getRestServiceMock(), ['user_id' => self::$currentUser->id]);

        $expectedData = $locale->formatName(self::$currentUser);
        $this->assertEquals($expectedData, $restReply['data']);
        $this->_user->setPreference('default_locale_name_format', $defaultPreference, 0, 'global');
        $this->_user->savePreferencesToDB();
        $this->_user->reloadPreferences();
    }

    /***
     * Test that we get the reportees assigned to the currentUser id
     *
     * @group forecastapi
     * @group forecasts
     * @covers ForecastsApi::getReportees
     */
    public function testReportees()
    {
        $api = new ForecastsApi();
        $restReply = $api->getReportees(SugarTestRestUtilities::getRestServiceMock(), ['user_id' => self::$currentUser->id, 'level' => '4']);

        $this->assertEquals($restReply['metadata']['id'], self::$currentUser->id, "currentUser's id was not found in the Expected place in the rest reply");

        // get the user ids from first, second, and third levels
        $firstLevel = [];
        $secondLevel = [];
        $thirdLevel = [];
        foreach ($restReply['children'] as $level1Child) {
            array_push($firstLevel, $level1Child['metadata']['id']);
            foreach ($level1Child['children'] as $level2Child) {
                $secondLevel[] = $level2Child['metadata']['id'];
                foreach ($level2Child['children'] as $level3Child) {
                    $thirdLevel[] = $level3Child['metadata']['id'];
                }
            }
        }

        // assertContains in case the order is ever jumbled
        $this->assertContains(self::$employee1->id, $firstLevel, "employee1's id was not found in the Expected place in the rest reply");
        $this->assertContains(self::$employee2->id, $firstLevel, "employee2's id was not found in the Expected place in the rest reply");
        $this->assertContains(self::$employee3->id, $secondLevel, "employee3's id was not found in the Expected place in the rest reply");
        $this->assertContains(self::$employee4->id, $thirdLevel, "employee4's id was not found in the Expected place in the rest reply");
    }

    /**
     * Test that a deleted user does not show up from the filter call
     *
     * @group forecastapi
     * @group forecasts
     * @covers ForecastsApi::getReportees
     */
    public function testDeletedReportees()
    {
        // delete one user for this test
        self::$employee2->deleted = 1;
        self::$employee2->save();
        $GLOBALS['db']->commit();

        $api = new ForecastsApi();
        $restReply = $api->getReportees(SugarTestRestUtilities::getRestServiceMock(), ['user_id' => self::$currentUser->id]);

        $fullNames = [];

        foreach ($restReply['children'] as $children) {
            array_push($fullNames, $children['data']);
        }

        $this->assertNotContains(self::$employee2->full_name, $fullNames, 'Deleted employee2 was found in the rest reply when it should not have been');

        // Undelete user if needed for other tests
        self::$employee2->deleted = 0;
        self::$employee2->save();
        $GLOBALS['db']->commit();
    }
}
