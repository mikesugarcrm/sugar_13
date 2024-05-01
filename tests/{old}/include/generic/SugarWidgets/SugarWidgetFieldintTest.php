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

class SugarWidgetFieldintTest extends TestCase
{
    /**
     * @var SugarWidgetFieldint
     */
    protected $widgetField;

    protected function setUp(): void
    {
        global $current_user;
        $layoutManager = new LayoutManager();
        $this->widgetField = new SugarWidgetFieldInt($layoutManager);
        $current_user = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        unset($this->widgetField);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @group SugarWidgetField
     * @group LessEqual
     */
    public function testQueryFilterLess_Equal()
    {
        $layout_def = ['name' => 'donotinvoiceuntil_c', 'table_key' => 'self', 'qualifier_name' => 'Less_Equal', 'input_name0' => '1', 'input_name1' => 'on', 'table_alias' => 'pordr_purchaseorders_cstm', 'column_key' => 'self:donotinvoiceuntil_c', 'type' => 'int'];
        $filter = $this->widgetField->queryFilterLess_Equal($layout_def);

        $this->assertEquals("pordr_purchaseorders_cstm.donotinvoiceuntil_c <= 1\n", $filter);
    }

    /**
     * @group SugarWidgetField
     * @group GreaterEqual
     */
    public function testQueryFilterGreater_Equal()
    {
        $layout_def = ['name' => 'donotinvoiceuntil_c', 'table_key' => 'self', 'qualifier_name' => 'Greater_Equal', 'input_name0' => '1', 'input_name1' => 'on', 'table_alias' => 'pordr_purchaseorders_cstm', 'column_key' => 'self:donotinvoiceuntil_c', 'type' => 'int'];
        $filter = $this->widgetField->queryFilterGreater_Equal($layout_def);

        $this->assertEquals("pordr_purchaseorders_cstm.donotinvoiceuntil_c >= 1\n", $filter);
    }
}
