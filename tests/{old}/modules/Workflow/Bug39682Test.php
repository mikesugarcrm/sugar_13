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

include_once 'include/workflow/alert_utils.php';

/**
 * Bug #39682
 * Email sent through a workflow: a customised multiselect field sends ^^ characters
 * @ticket 39682
 */
class Bug39682Test extends TestCase
{
    /**
     * Test tries to get string from multiselect values and asserts that string doesn't contains ^
     * @group 39682
     */
    public function testMultienumFieldsDecode()
    {
        $aCase = new aCase();
        $aCase->field_defs['new_field_here_c'] = [
            'type' => 'multienum',
            'name' => 'new_field_here_c',
        ];
        $targetBody = '&lt;p&gt;{::future::Cases::new_field_here_c::}&lt;/p&gt;
                       &lt;p&gt;{::past::Cases::new_field_here_c::}&lt;/p&gt;
                       &lt;p&gt;{::future::Cases::new_field_here_c::}&lt;/p&gt;';
        $componentArray = [
            'Cases' => [
                'new_field_here_c_future' => [
                    'name' => 'new_field_here_c',
                    'value_type' => 'future',
                    'original' => '{::future::Cases::new_field_here_c::}',
                ],
                'new_field_here_c_past' => [
                    'name' => 'new_field_here_c',
                    'value_type' => 'past',
                    'original' => '{::past::Cases::new_field_here_c::}',
                ],
            ],
        ];
        $aCase->new_field_here_c = '^Analyst^,^Competitor^,^Customer^,^Integrator^';
        $resultBody = reconstruct_target_body($aCase, $targetBody, $componentArray);

        $this->assertStringNotContainsString('^,^', $resultBody);
    }
}
