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

class ForecastHooksTest extends TestCase
{
    public function testSetCommitStageWhenNotSetup()
    {
        $hook = new MockForecastHooks();

        /* @var $bean Opportunity */
        $bean = $this->createPartialMock('Opportunity', ['save']);
        $bean->probability = 90;

        /* @var $hook ForecastHooks */
        $hook->setCommitStageIfEmpty($bean, 'before_save');

        $this->assertEmpty($bean->commit_stage);
    }

    public function testSetCommitStageToInclude()
    {
        $hook = new MockForecastHooks();

        $settings = [
            'forecast_ranges' => 'show_binary',
            'show_binary_ranges' => [
                'include' => [
                    'min' => 70,
                    'max' => 100,
                    'in_included_total' => true,
                ],
                'exclude' => [
                    'min' => 0,
                    'max' => 69,
                ],
            ],
        ];

        /* @var $bean Opportunity */
        $bean = $this->createPartialMock('Opportunity', ['save']);
        $bean->probability = 90;

        /* @var $hook ForecastHooks */
        $hook::$settings = $settings;
        $hook->setCommitStageIfEmpty($bean, 'before_save');

        $this->assertEquals('include', $bean->commit_stage);
    }

    public function testSetCommitStageToExclude()
    {
        $hook = new MockForecastHooks();

        $settings = [
            'forecast_ranges' => 'show_binary',
            'show_binary_ranges' => [
                'include' => [
                    'min' => 70,
                    'max' => 100,
                    'in_included_total' => true,
                ],
                'exclude' => [
                    'min' => 0,
                    'max' => 69,
                ],
            ],
        ];

        /* @var $bean Opportunity */
        $bean = $this->createPartialMock('Opportunity', ['save']);
        $bean->probability = 50;

        /* @var $hook ForecastHooks */
        $hook::$settings = $settings;
        $hook->setCommitStageIfEmpty($bean, 'before_save');

        $this->assertEquals('exclude', $bean->commit_stage);
    }

    public function testSetCommitStageDoesNotChangeValue()
    {
        $hook = new MockForecastHooks();

        $settings = [
            'forecast_ranges' => 'show_binary',
            'show_binary_ranges' => [
                'include' => [
                    'min' => 70,
                    'max' => 100,
                    'in_included_total' => true,
                ],
                'exclude' => [
                    'min' => 0,
                    'max' => 69,
                ],
            ],
        ];

        /* @var $bean Opportunity */
        $bean = $this->createPartialMock('Opportunity', ['save']);
        $bean->probability = 50;
        $bean->commit_stage = 'include';

        /* @var $hook ForecastHooks */
        $hook::$settings = $settings;
        $hook->setCommitStageIfEmpty($bean, 'before_save');

        $this->assertEquals('include', $bean->commit_stage);
    }

    public function testSetBestWorstEqualToLikelyAmountWorks()
    {
        $hook = new MockForecastHooks();

        /** @var Opportunity $bean */
        $bean = $this->createPartialMock('Opportunity', ['save']);
        $bean->amount = 500;
        $bean->best_case = 600;
        $bean->worst_case = 400;
        $bean->sales_stage = 'Closed Won';

        /* @var $hook ForecastHooks */
        $hook->setBestWorstEqualToLikelyAmount($bean, 'before_save');

        $this->assertEquals($bean->amount, $bean->best_case);
        $this->assertEquals($bean->amount, $bean->worst_case);
    }

    public function testSetBestWorstEqualToLikelyAmountDoesntCopyValues()
    {
        $hook = new MockForecastHooks();

        /** @var Opportunity $bean */
        $bean = $this->createPartialMock('Opportunity', ['save']);
        $bean->amount = 500;
        $bean->best_case = 600;
        $bean->worst_case = 400;
        $bean->sales_stage = 'Prospecting';

        /* @var $hook ForecastHooks */
        $hook->setBestWorstEqualToLikelyAmount($bean, 'before_save');

        $this->assertEquals(600, $bean->best_case);
        $this->assertEquals(400, $bean->worst_case);
    }
}

class MockForecastHooks extends ForecastHooks
{
    public static function isForecastSetup()
    {
        return true;
    }

    public static function getForecastClosedStages()
    {
        return ['Closed Won'];
    }
}
