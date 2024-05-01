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

require_once 'include/workflow/workflow_utils.php';

class Bug43572Test extends TestCase
{
    public function testGlueDate()
    {
        $condition = new Expression();
        $condition->lhs_field = 'date_closed';
        $condition->exp_type = 'datetime';
        $condition->operator = 'Less Than';
        $condition->ext1 = 172800;
        $glueWorkflow = new WorkFlowGlue();
        $actualCondition = $glueWorkflow->glue_date('future', $condition, true);
        // Bug 50258 - fixed date logic
        $expectedConditionChunk = preg_quote(
            'TimeDate::getInstance()->fromDb($focus->date_closed)->getTimestamp() > (time() - 172800)',
            '~'
        );
        $matched = preg_match("~$expectedConditionChunk~i", $actualCondition);
        $this->assertEquals(1, $matched);
    }
}
