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
use Sugarcrm\Sugarcrm\Reports\Types;

/**
 * Test Changes to a report don't affect already-create report dashlets
 */
class BugCA1448Test extends TestCase
{
    private $reportBean;
    private $reportId = '123';
    private $dateModified = '2022-10-12';

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->reportBean = BeanFactory::newBean('Reports');
        $this->reportBean->id = $this->reportId;
        $this->reportBean->date_modified = $this->dateModified;

        BeanFactory::registerBean($this->reportBean);
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        BeanFactory::unregisterBean($this->reportBean);
    }

    /**
     * Test if filter data returns new parameters
     */
    public function testGetFilterData()
    {
        $data = [
            'record' => $this->reportId,
        ];

        $ignoreBuildReportDef = true;
        $reporter = new Types\Reporter($data, $ignoreBuildReportDef);
        $filterData = $reporter->getFilterData();

        $this->assertEquals($filterData['reportId'], $this->reportId, 'Should return the report id');
        $this->assertEquals($filterData['dateModified'], $this->dateModified, 'Should return the date modified');
    }


    /**
     * Test if buildReportDef will throw the correctly exception
     */
    public function testBuildReportDef()
    {
        $data = [
            'record' => 'invalid_id',
        ];

        $ignoreBuildReportDef = false;
        $invalidBean = false;
        $expectedResult = true;

        try {
            new Types\Reporter($data, $ignoreBuildReportDef);
        } catch (SugarApiExceptionNotFound $e) {
            $invalidBean = true;
        }

        $this->assertEquals($expectedResult, $invalidBean);
    }
}
