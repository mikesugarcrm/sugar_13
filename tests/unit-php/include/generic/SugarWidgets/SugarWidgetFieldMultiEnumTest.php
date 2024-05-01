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
 * Class SugarWidgetFieldEnumTest
 * @package Sugarcrm\SugarcrmTestsUnit\inc\generic\SugarWidgets
 * @coversDefaultClass \SugarWidgetFieldMultiEnum
 */
class SugarWidgetFieldMultiEnumTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Report|mixed
     */
    public $reporter;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\SugarWidgetFieldMultiEnum|mixed
     */
    public $widgetField;

    protected function setUp(): void
    {
        $this->reporter = $this->createPartialMock(\Report::class, []);

        // this isn't particular to MySQL or to ext-mysqli, but MysqliManager isn't abstract, so it's easier to mock.
        // Both MySQLManager and DBManager are abstract and so mocking them is more strenuous
        $db = $this->createPartialMock(\MysqliManager::class, ['quoted']);
        $this->reporter->db = $db;

        // NOTE: some of the functions in this class use the Global DB instead of the reporter DB
        // You can't mock that without confusing the Ultralite cache, so instead we'll just
        // decline from testing any of those for now. :(

        $lm = $this->createPartialMock(\LayoutManager::class, []);
        $lm->setAttributePtr('reporter', $this->reporter);
        $this->widgetField = $this->createPartialMock(\SugarWidgetFieldMultiEnum::class, ['getInputValue']);
        $this->widgetField->layout_manager = $lm;
        TestReflection::setProtectedValue($this->widgetField, 'reporter', $this->reporter);
    }

    /**
     * @covers ::queryFilterIs
     */
    public function testQueryFilterIs()
    {
        $expected = "accounts_cstm.custom_multi_c = \"Value1\"\n";
        $layoutDef = $layoutDef = [
            'name' => 'custom_multi_c',
            'table_key' => 'self',
            'qualifier_name' => 'is',
            'table_alias' => 'accounts_cstm',
            'input_name0' => 'Value1',
            'column_key' => 'self:custom_multi',
            'type' => 'multienum',
        ];
        $this->reporter->db->expects($this->once())
            ->method('quoted')
            ->willReturnCallback(function ($str) {
                return '"' . $str . '"';
            });
        $filter = $this->widgetField->queryFilteris($layoutDef);
        $this->assertEquals($expected, $filter);
    }
}
