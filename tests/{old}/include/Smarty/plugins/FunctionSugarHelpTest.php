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

require_once 'include/SugarSmarty/plugins/function.sugar_help.php';

class FunctionSugarHelpTest extends TestCase
{
    /**
     * @var Sugar_Smarty
     */
    private $smarty;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('app_strings');
        $this->smarty = new Sugar_Smarty();
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    public function providerSpecialCharactersHandledInTextParameter()
    {
        return [
            [
                'dog "the" bounty hunter & friends are <b>cool</b>',
                'dog &quot;the&quot; bounty hunter &amp; friends are &lt;b&gt;cool&lt;/b&gt;',
            ],
            [
                "dog 'the' bounty hunter",
                "dog \'the\' bounty hunter",
            ],
        ];
    }

    /**
     * @dataProvider providerSpecialCharactersHandledInTextParameter
     */
    public function testSpecialCharactersHandledInTextParameter($string, $returnedString)
    {
        $this->assertStringContainsString(
            $returnedString,
            smarty_function_sugar_help(['text' => $string], $this->smarty)
        );
    }

    public function testExtraParametersAreAdded()
    {
        $this->assertStringContainsString(
            ",'foo','bar'",
            smarty_function_sugar_help([
                'text' => 'my string',
                'myPos' => 'foo',
                'atPos' => 'bar',
            ], $this->smarty)
        );
    }
}
