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
 * Test for SugarWidgetReportFieldmultienum.
 *
 * Class SugarWidgetReportFieldmultienumTest
 */
class SugarWidgetReportFieldmultienumTest extends TestCase
{
    /**
     * {@inheritDoc}
     */
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
    }

    /**
     * {@inheritDoc}
     */
    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Need to check that SugarWidgetFieldMultiEnum::_get_column_select calls DBManager::convert
     */
    public function testGetColumnSelect()
    {
        $def = [
            'name' => 'test',
        ];
        $report = $this->createMock('Report');
        $db = DBManagerFactory::getInstance();
        $dbMock = $this->createPartialMock(get_class($db), ['convert']);
        $dbMock->expects($this->once())
            ->method('convert')
            ->with($this->equalTo('test'), $this->equalTo('text2char'));

        $lm = new LayoutManager();
        $report->db = $dbMock;
        $lm->setAttribute('reporter', $report);
        $widget = new SugarWidgetFieldMultiEnum($lm);
        $widget->_get_column_select($def);
    }
}
