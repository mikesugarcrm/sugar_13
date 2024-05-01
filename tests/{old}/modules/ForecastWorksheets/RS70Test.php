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


use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * RS-70: Prepare ForecastWorksheets Module
 */
class RS70Test extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        SugarTestForecastUtilities::setUpForecastConfig([
            'forecast_by' => 'RevenueLineItems',
        ]);
    }

    protected function tearDown(): void
    {
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestHelper::tearDown();
    }

    /**
     * Test covers behavior of saveWorksheetOpportunity method
     */
    public function testSaveWorksheetOpportunity()
    {
        /** @var MockObject|Opportunity $parent */
        $parent = $this->getMockBuilder('Opportunity')->setMethods(['save'])->getMock();
        $parent->expects($this->once())->method('save');
        $parent->id = create_guid();
        $parent->new_with_id = true;
        BeanFactory::registerBean($parent->module_name, $parent, $parent->id);

        $bean = new ForecastWorksheet();
        $bean->parent_type = $parent->module_name;
        $bean->parent_id = $parent->id;

        $bean->probability = create_guid();
        $bean->best_case = create_guid();
        $bean->likely_case = create_guid();
        $bean->sales_stage = create_guid();
        $bean->commit_stage = create_guid();
        $bean->worst_case = create_guid();

        $bean->saveWorksheet();

        $this->assertEquals($bean->probability, $parent->probability);
        $this->assertEquals($bean->best_case, $parent->best_case);
        $this->assertEquals($bean->likely_case, $parent->amount);
        $this->assertEquals($bean->sales_stage, $parent->sales_stage);
        $this->assertEquals($bean->commit_stage, $parent->commit_stage);
        $this->assertEquals($bean->worst_case, $parent->worst_case);
    }

    /**
     * Test covers behavior of saveWorksheetRevenueLineItem method
     */
    public function testSaveWorksheetRevenueLineItem()
    {
        /** @var MockObject|RevenueLineItem $parent */
        $parent = $this->getMockBuilder('RevenueLineItem')->setMethods(['save'])->getMock();
        $parent->expects($this->once())->method('save');
        $parent->id = create_guid();
        $parent->new_with_id = true;
        BeanFactory::registerBean($parent->module_name, $parent, $parent->id);

        $bean = new ForecastWorksheet();
        $bean->parent_type = $parent->module_name;
        $bean->parent_id = $parent->id;

        $bean->probability = create_guid();
        $bean->best_case = create_guid();
        $bean->likely_case = create_guid();
        $bean->sales_stage = create_guid();
        $bean->commit_stage = create_guid();
        $bean->worst_case = create_guid();
        $bean->date_closed = create_guid();

        $bean->saveWorksheet();

        $this->assertEquals($bean->probability, $parent->probability);
        $this->assertEquals($bean->best_case, $parent->best_case);
        $this->assertEquals($bean->likely_case, $parent->likely_case);
        $this->assertEquals($bean->date_closed, $parent->date_closed);
        $this->assertEquals($bean->sales_stage, $parent->sales_stage);
        $this->assertEquals($bean->commit_stage, $parent->commit_stage);
        $this->assertEquals($bean->worst_case, $parent->worst_case);
    }

    /**
     * Test covers behavior of setWorksheetArgs method
     */
    public function testSetWorksheetArgs()
    {
        $bean = new ForecastWorksheet();
        $agrs = [
            'id' => 'id1',
            'test' => 'test1',
        ];
        $bean->setWorksheetArgs($agrs);
        $this->assertEquals($agrs, $bean->args);
        foreach ($agrs as $k => $v) {
            $this->assertEquals($v, $bean->$k);
        }
    }

    /**
     * Test coverts behavior of saveRelatedOpportunity method
     */
    public function testSaveRelatedOpportunity()
    {
        $opportunity = new Opportunity();
        $opportunity->id = create_guid();
        $opportunity->account_id = create_guid();

        /** @var MockObject|ForecastWorksheet $bean */
        $bean = $this->createPartialMock('ForecastWorksheet', ['retrieve_by_string_fields', 'getRelatedName', 'copyValues', 'save', 'removeMigratedRow']);
        $bean->expects($this->once())->method('retrieve_by_string_fields');
        $bean->expects($this->once())->method('getRelatedName')->with($this->equalTo('Accounts'), $this->equalTo($opportunity->account_id))->will($this->returnValue('Account ' . self::class));
        $bean->expects($this->once())->method('copyValues')->with($this->anything(), $this->equalTo($opportunity));
        $bean->expects($this->once())->method('save');
        $bean->expects($this->once())->method('removeMigratedRow')->with($this->equalTo($opportunity));

        $bean->saveRelatedOpportunity($opportunity, false);
        $this->assertEquals($opportunity->module_name, $bean->parent_type);
        $this->assertEquals($opportunity->id, $bean->parent_id);
        $this->assertEquals(1, $bean->draft);
    }

    /**
     * Test covers behavior of saveRelatedProduct method
     */
    public function testSaveRelatedProduct()
    {
        $parent = new RevenueLineItem();
        $parent->id = create_guid();

        /** @var MockObject|ForecastWorksheet $bean */
        $bean = $this->createPartialMock('ForecastWorksheet', ['retrieve_by_string_fields', 'getRelatedName', 'copyValues', 'removeMigratedRow', 'save']);
        $bean->expects($this->once())->method('retrieve_by_string_fields');
        $bean->expects($this->never())->method('getRelatedName');
        $bean->expects($this->once())->method('copyValues')->with($this->isType('array'), $this->equalTo($parent));
        $bean->expects($this->once())->method('removeMigratedRow')->with($this->equalTo($parent));
        $bean->expects($this->once())->method('save');

        $bean->saveRelatedProduct($parent);
        $this->assertEquals($parent->module_name, $bean->parent_type);
        $this->assertEquals($parent->id, $bean->parent_id);
        $this->assertEquals(1, $bean->draft);
    }

    /**
     * Test covers behavior of saveRelatedProductFillNames method
     *
     * @dataProvider getDataForTestSaveRelatedProductFillNames
     *
     * @param string $module
     * @param        $propertyId
     * @param        $propertyName
     *
     * @internal     param string $property
     */
    public function testSaveRelatedProductFillNames($module, $propertyId, $propertyName)
    {
        $parent = new RevenueLineItem();
        $parent->id = create_guid();
        $parent->$propertyId = create_guid();

        /** @var MockObject|ForecastWorksheet $bean */
        $bean = $this->createPartialMock('ForecastWorksheet', ['retrieve_by_string_fields', 'getRelatedName', 'copyValues', 'removeMigratedRow', 'save']);
        $bean->expects($this->any())->method('retrieve_by_string_fields');
        $bean->expects($this->any())->method('copyValues');
        $bean->expects($this->any())->method('removeMigratedRow');
        $bean->expects($this->any())->method('save');
        $bean->expects($this->once())->method('getRelatedName')->with($this->equalTo($module), $this->equalTo($parent->$propertyId))->will($this->returnValue($module));

        $bean->saveRelatedProduct($parent);
        $this->assertEquals($module, $parent->$propertyName);
    }

    /**
     * Data provider for testSaveRelatedProductFillNames
     * @return array
     */
    public function getDataForTestSaveRelatedProductFillNames()
    {
        return [
            ['Accounts', 'account_id', 'account_name'],
            ['Opportunities', 'opportunity_id', 'opportunity_name'],
            ['ProductTemplates', 'product_template_id', 'product_template_name'],
            ['ProductCategories', 'category_id', 'category_name'],
        ];
    }

    /**
     * Test covers behavior of timePeriodHasMigrated method
     *
     * @dataProvider getDataForTestTimePeriodHasMigrated
     *
     * @param string $worksheetDate
     * @param string $objDate
     * @param bool $expected
     */
    public function testTimePeriodHasMigrated($worksheetDate, $objDate, $expected)
    {
        SugarTestTimePeriodUtilities::createTimePeriod('2013-01-01', '2013-05-01');
        SugarTestTimePeriodUtilities::createTimePeriod('2013-05-01', '2013-10-01');
        $bean = new ForecastWorksheet();
        $actual = SugarTestReflection::callProtectedMethod($bean, 'timeperiodHasMigrated', [
            $worksheetDate,
            $objDate,
        ]);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Data provider for testTimePeriodHasMigrated
     *
     * @return array
     */
    public function getDataForTestTimePeriodHasMigrated()
    {
        return [
            [
                '2013-05-05 00:00:00',
                '2013-05-05 00:00:00',
                false,
            ],
            [
                '2013-01-05 00:00:00',
                '2013-05-05 00:00:00',
                true,
            ],
        ];
    }

    /**
     * Test covers behavior of copyValues method
     */
    public function testCopyValues()
    {
        $bean = new ForecastWorksheet();
        $account = new Account();
        $account->id = 'id1';
        $account->name = 'name1';
        SugarTestReflection::callProtectedMethod($bean, 'copyValues', [
            ['id', 'name'],
            $account,
        ]);
        $this->assertEquals($account->id, $bean->id);
        $this->assertEquals($account->name, $bean->name);
    }

    /**
     * Test covers behavior of commitWorksheet method
     */
    public function testCommitWorksheet()
    {
        $tp = SugarTestTimePeriodUtilities::createTimePeriod();

        $acc = SugarTestAccountUtilities::createAccount();
        $opp = SugarTestOpportunityUtilities::createOpportunity(null, $acc);

        $opp->assigned_user_id = $GLOBALS['current_user']->id;
        $opp->date_closed = $tp->start_date;
        $opp->save();

        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli->opportunity_id = $opp->id;
        $rli->account_id = $acc->id;
        $rli->assigned_user_id = $GLOBALS['current_user']->id;
        $rli->date_closed = $tp->start_date;
        $rli->save();

        $bean = $this->createPartialMock('ForecastWorksheet', ['createUpdateForecastWorksheetJob',
            'removeReassignedItems',
        ]);

        $bean->expects($this->any())->method('createUpdateForecastWorksheetJob');

        $bean->expects($this->once())
            ->method('removeReassignedItems');

        $actual = SugarTestReflection::callProtectedMethod($bean, 'commitWorksheet', [
            $GLOBALS['current_user']->id,
            $tp->id,
        ]);
        $this->assertTrue($actual);

        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
    }

    /**
     * Test covers that execution of queries doesn't gather an error
     */
    public function testReassignForecast()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->reports_to_id = $GLOBALS['current_user']->id;
        $user->save();

        $bean = new ForecastWorksheet();
        $bean->assigned_user_id = $GLOBALS['current_user']->id;
        $bean->save();

        SugarTestWorksheetUtilities::setCreatedWorksheet([$bean->id]);

        $actual = SugarTestReflection::callProtectedMethod($bean, 'reassignForecast', [
            $GLOBALS['current_user']->id,
            $user->id,
        ]);
        $this->assertNotEmpty($actual);
    }

    /**
     * Test covers that execution of queries doesn't gather an error
     */
    public function testWorksheetTotals()
    {
        $tp = SugarTestTimePeriodUtilities::createTimePeriod();

        $bean = new ForecastWorksheet();
        $actual = $bean->worksheetTotals($tp->id, $GLOBALS['current_user']->id);
        $this->assertNotEmpty($actual);
    }
}
