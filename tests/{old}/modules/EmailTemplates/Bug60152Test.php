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
 * Bug #60152
 * Using Alert Template Variables in the TinyMCE Editor Link replaces '{' and '}' with '%7B' and '%7D'
 *
 * @author mgusev@sugarcrm.com
 * @ticked 60152
 */
class Bug60152Test extends TestCase
{
    /**
     * Test asserts that body_html has variables after cleanBean call
     *
     * @group 60152
     * @dataProvider dataProvider
     * @return void
     */
    public function testCleanBean($html, $needle)
    {
        $bean = new EmailTemplate();
        $bean->body_html = $html;
        $bean->cleanBean();
        $this->assertStringContainsString($needle, $bean->body_html);
    }

    public static function dataProvider()
    {
        return [
            [
                '<a href="{::test::}">test</a>',
                '{::test::}',
            ],
        ];
    }
}
