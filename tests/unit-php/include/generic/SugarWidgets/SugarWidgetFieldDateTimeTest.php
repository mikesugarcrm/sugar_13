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

/** @noinspection PhpUndefinedFieldInspection */

namespace Sugarcrm\SugarcrmTestsUnit\inc\generic\SugarWidgets;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class SugarWidgetFieldDateTimeTest
 * @package Sugarcrm\SugarcrmTestsUnit\inc\generic\SugarWidgets
 * @coversDefaultClass \SugarWidgetFieldDateTime
 */
class SugarWidgetFieldDateTimeTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Report|mixed
     */
    public $reporter;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\SugarWidgetFieldDateTime|mixed
     */
    public $widgetField;

    protected function setUp(): void
    {
        $this->reporter = $this->createPartialMock(\Report::class, []);
        $db = $this->createPartialMock(\MysqliManager::class, []);
        $this->reporter->db = $db;
        $lm = $this->createPartialMock(\LayoutManager::class, []);
        $lm->setAttributePtr('reporter', $this->reporter);
        $this->widgetField = $this->createPartialMock(\SugarWidgetFieldDateTime::class, ['getFiscalStartDate', '_get_column_select']);
        $this->widgetField->layout_manager = $lm;
        TestReflection::setProtectedValue($this->widgetField, 'reporter', $this->reporter);
    }

    /**
     * @covers ::getNormalizedDate
     * @dataProvider normalizeDataProvider
     */
    public function testGetNormalizedDate($fiscalDateStart, $columnFunction, $expected)
    {
        $layoutDef = [
            'name' => 'date_entered',
            'table_key' => 'self',
            'column_function' => $columnFunction,
            'type' => 'datetime',
        ];

        $timedate = \TimeDate::getInstance();
        $fiscalDate = $timedate->fromString($fiscalDateStart);

        $this->widgetField->expects($this->once())
            ->method('getFiscalStartDate')
            ->will($this->returnValue($fiscalDate));

        $this->widgetField->expects($this->once())
            ->method('_get_column_select')
            ->will($this->returnValue('date_entered'));

        $class = new \ReflectionClass('SugarWidgetFieldDateTime');
        $method = $class->getMethod('getNormalizedDate');
        $method->setAccessible(true);
        $date = $method->invokeArgs($this->widgetField, [$layoutDef]);

        $this->assertEquals($expected, $date);
    }

    /**
     * DataProvider for TestGetNormalizedDate
     */
    public function normalizeDataProvider(): array
    {
        return [
            [
                '2022-01-01',
                '',
                'date_entered',
            ],
            [
                '2022-04-01',
                '',
                'DATE_ADD(date_entered, INTERVAL -90 DAY)',
            ],
            [
                '2022-04-01',
                'fiscalYear',
                'DATE_ADD(DATE_ADD(date_entered, INTERVAL -90 DAY), INTERVAL +1 YEAR)',
            ],
        ];
    }
}
