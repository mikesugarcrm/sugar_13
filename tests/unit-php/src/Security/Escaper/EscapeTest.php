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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Escaper;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Security\Escaper\Escape;

/**
 * @coversClass Sugarcrm\Sugarcrm\Security\XssEscaper\Escape
 */
class EscapeTest extends TestCase
{
    public function testEscapeHtml(): void
    {
        $this->assertEquals(
            '&lt;script&gt;alert(&quot;test&quot;)&lt;/script&gt;',
            Escape::html('<script>alert("test")</script>')
        );
    }

    public function testEscapeHtmlAttr(): void
    {
        $this->assertEquals(
            '&lt;script&gt;alert&#x28;&quot;test&quot;&#x29;&lt;&#x2F;script&gt;',
            Escape::htmlAttr('<script>alert("test")</script>')
        );
    }

    public function testEscapeJs(): void
    {
        $this->assertEquals(
            '\x3Cscript\x3Ealert\x28\x22test\x22\x29\x3C\x2Fscript\x3E',
            Escape::js('<script>alert("test")</script>')
        );
    }

    public function testEscapeUrl(): void
    {
        $this->assertEquals(
            '%3Cscript%3Ealert%28%22test%22%29%3C%2Fscript%3E',
            Escape::url('<script>alert("test")</script>')
        );
    }

    public function testEscapeCss(): void
    {
        $this->assertEquals(
            '\3C script\3E alert\28 \22 test\22 \29 \3C \2F script\3E ',
            Escape::css('<script>alert("test")</script>'),
        );
    }
}
