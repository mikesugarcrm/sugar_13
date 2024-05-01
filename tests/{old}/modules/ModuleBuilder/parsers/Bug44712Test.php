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

class Bug44712Test extends TestCase
{
    public function testTranslateLabel()
    {
        $activitiesRelationship = new ActivitiesRelationship([]);
        $vardef = SugarTestReflection::callProtectedMethod($activitiesRelationship, 'getLinkFieldDefinition', ['Tasks', 'abc_MyCustomBasic_Activities_Tasks']);
        $this->assertEquals('LBL_ABC_MYCUSTOMBASIC_ACTIVITIES_TASKS_FROM_TASKS_TITLE', $vardef['vname'], "Assert that vardef['vname'] is set to LBL_ABC_MYCUSTOMBASIC_ACTIVITIES_FROM_TASKS_TITLE");
    }
}
