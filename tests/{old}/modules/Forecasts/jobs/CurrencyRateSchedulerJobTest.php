<?php

//TODO: fix this up for when expected opps is added back in 6.8 - https://sugarcrm.atlassian.net/browse/SFA-255
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

class CurrencyRateSchedulerJobTest extends TestCase
{
    private $currency;
    private $opportunity;
    private $opportunityClosed;
    private $quota;
    private $forecast;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        SugarTestForecastUtilities::setUpForecastConfig();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
    }

    protected function setUp(): void
    {
        global $current_user;
        $this->currency = SugarTestCurrencyUtilities::createCurrency('UpdateBaseRateSchedulerJob', 'UBRSJ', 'UBRSJ', 1.234);

        $this->opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $this->opportunity->currency_id = $this->currency->id;
        $this->opportunity->sales_stage = 'Prospecting';
        $this->opportunity->save();

        $this->opportunityClosed = SugarTestOpportunityUtilities::createOpportunity();
        $this->opportunityClosed->sales_stage = Opportunity::STAGE_CLOSED_WON;
        $this->opportunityClosed->currency_id = $this->currency->id;
        $this->opportunityClosed->save();

        $this->quota = SugarTestQuotaUtilities::createQuota(500);
        $this->quota->currency_id = $this->currency->id;
        $this->quota->save();

        $timeperiod = SugarTestTimePeriodUtilities::createTimePeriod();

        $this->forecast = SugarTestForecastUtilities::createForecast($timeperiod, $current_user);
        // currency is always base, set by forecast save()
        $this->forecast->save();
    }

    protected function tearDown(): void
    {
        SugarTestJobQueueUtilities::removeAllCreatedJobs();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
    }

    /**
     * @group forecasts
     */
    public function testCurrencyRateDefinitions()
    {
        $test = new UpdateRateTest();
        $test->addRateColumnDefinition('foo', 'bar');
        $test->addRateColumnDefinition('foo', 'baz');
        $test->addRateColumnDefinition('foo', 'biz');
        $rates = $test->getRateColumnDefinitions('foo');
        $this->assertEquals(['bar', 'baz', 'biz'], $rates);
        $test->removeRateColumnDefinition('foo', 'baz');
        $rates = $test->getRateColumnDefinitions('foo');
        $this->assertEquals(['bar', 'biz'], $rates);

        $test->addUsDollarColumnDefinition('foo', 'bar', 'bar_usdollar');
        $test->addUsDollarColumnDefinition('foo', 'baz', 'baz_usdollar');
        $test->addUsDollarColumnDefinition('foo', 'biz', 'biz_usdollar');
        $rates = $test->getUsDollarColumnDefinitions('foo');
        $this->assertEquals([
            'bar' => 'bar_usdollar',
            'baz' => 'baz_usdollar',
            'biz' => 'biz_usdollar',
        ], $rates);
        $test->removeUsDollarColumnDefinition('foo', 'baz');
        $rates = $test->getUsDollarColumnDefinitions('foo');
        $this->assertEquals([
            'bar' => 'bar_usdollar',
            'biz' => 'biz_usdollar',
        ], $rates);
    }
}

class UpdateRateTest extends CurrencyRateUpdateAbstract
{
}
