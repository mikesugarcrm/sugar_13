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

class GlueTest extends TestCase
{
    private $toClean = [];

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        foreach ($this->toClean as $key => $value) {
            $GLOBALS['db']->query("DELETE FROM $key WHERE id = '$value'");
        }

        SugarTestHelper::tearDown();
    }

    /**
     * Make sure that write_escape() properly escapes the value, and
     * calls stripslashes() on it
     *
     * @dataProvider dataProviderWriteEscape
     * @param $value - To be escaped for use in PHP
     * @param $expected - Value after using it as PHP code
     */
    public function testWriteEscape($value, $expected)
    {
        $wfg = new WorkFlowGlue();
        $actual = $wfg->write_escape($value);

        eval("\$actual = $actual;");

        $this->assertEquals($expected, $actual, "write_escape() didn't return properly escaped value for use in PHP");
    }

    public static function dataProviderWriteEscape()
    {
        return [
            [
                'A strange string "that is $being" &#64;, &amp; compared',
                'A strange string "that is $being" @, & compared',
            ],
            [
                "A strange string 'that is &#36;being' escaped, &#38; compared",
                "A strange string 'that is \$being' escaped, & compared",
            ],
        ];
    }

    /**
     * Make sure getCompareText returns proper PHP code for different field types
     *
     * @dataProvider dataProviderGetCompareText
     * @param $field - Field the WorkFlowTriggerShells object is on
     * @param $is_equal
     * @param $expected
     */
    public function testGetCompareText($field, $is_equal, $expected)
    {
        $workflow = BeanFactory::newBean('WorkFlow');
        $workflow->base_module = 'Opportunities';
        $workflow->save();
        $this->toClean['workflow'] = $workflow->id;

        $workflowShell = BeanFactory::newBean('WorkFlowTriggerShells');
        $workflowShell->parent_id = $workflow->id;
        $workflowShell->field = $field;
        $workflowShell->save();
        $this->toClean['workflow_triggershells'] = $workflowShell->id;

        $class = new ReflectionClass('WorkFlowGlue');
        $method = $class->getMethod('getCompareText');
        $method->setAccessible(true);
        $args = [
            $workflowShell,
            $is_equal,
        ];
        $output = $method->invokeArgs(new WorkFlowGlue(), $args);

        $this->assertStringContainsString($expected, $output);
    }

    public static function dataProviderGetCompareText()
    {
        return [
            [
                'date_modified',
                true,
                "\$GLOBALS['timedate']->to_display_date_time(\$focus->fetched_row['date_modified']) === " .
                "\$GLOBALS['timedate']->to_display_date_time(\$focus->date_modified)",
            ],
            [
                'description',
                false,
                "\$focus->fetched_row['description'] !== \$focus->description",
            ],
            [
                'probability',
                true,
                "\$focus->fetched_row['probability'] == \$focus->probability",
            ],
            [
                'date_closed',
                false,
                "\$GLOBALS['timedate']->to_display_date(\$focus->fetched_row['date_closed']) !== " .
                "\$GLOBALS['timedate']->to_display_date(\$focus->date_closed)",
            ],
        ];
    }
}
