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

require_once 'include/generic/SugarWidgets/SugarWidgetReportField.php';

class Bug36246Test extends TestCase
{
    public function testIfWidgetFieldUrlReturnsALink()
    {
        $layoutManager = new LayoutManager();
        $fieldurl = $this->getMockBuilder('SugarWidgetFieldURL')
            ->setConstructorArgs([&$layoutManager])
            ->setMethods(['_get_list_value'])
            ->getMock();
        $fieldurl->expects($this->any())
            ->method('_get_list_value')
            ->will($this->returnValue('sugarcrm.com'));
        $link = $fieldurl->displayList([]);
        $this->assertMatchesRegularExpression("|<a([^>]*)href=\"sugarcrm.com\"([^>]*)>sugarcrm.com<\/a>|", $link, 'SugarWidgetFieldurl should return a link');
    }
}
